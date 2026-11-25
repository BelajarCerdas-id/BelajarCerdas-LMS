mod token;

use std::{
    sync::{
        atomic::{AtomicUsize, Ordering},
        Arc,
    },
    time::{Duration, SystemTime, UNIX_EPOCH},
};

use axum::{
    extract::{
        ws::{Message, WebSocket, WebSocketUpgrade},
        Query, State,
    },
    http::StatusCode,
    response::IntoResponse,
    routing::get,
    Router,
};
use futures_util::{SinkExt, StreamExt};
use serde::Deserialize;
use tokio::sync::broadcast;
use tracing::{info, warn};

use token::{Claims, Secret};

const CHANNEL: &str = "proctor:events";
const PING_EVERY: Duration = Duration::from_secs(25);
const MAX_FRAME_BYTES: usize = 4096;
// tungstenite defaults to 128 KiB per socket; frames here are tiny.
const SOCKET_BUFFER_BYTES: usize = 8 * 1024;
const BROADCAST_BACKLOG: usize = 4096;

pub struct Event {
    attempt_id: Option<i64>,
    assessment_id: Option<i64>,
    user_id: Option<i64>,
    school_id: Option<i64>,
    students_only: bool,
    raw: String,
}

#[derive(Deserialize)]
struct EventIds {
    attempt_id: Option<i64>,
    assessment_id: Option<i64>,
    user_id: Option<i64>,
    school_id: Option<i64>,
    audience: Option<String>,
}

impl Event {
    fn parse(raw: String) -> Option<Self> {
        let ids: EventIds = serde_json::from_str(&raw).ok()?;
        let students_only = ids.audience.as_deref() == Some("students");
        Some(Self { attempt_id: ids.attempt_id, assessment_id: ids.assessment_id, user_id: ids.user_id, school_id: ids.school_id, students_only, raw })
    }
}

#[derive(Clone)]
struct AppState {
    secret: Arc<Secret>,
    events: broadcast::Sender<Arc<Event>>,
    connections: Arc<AtomicUsize>,
    max_connections: usize,
}

#[derive(Deserialize)]
struct WsQuery {
    token: String,
}

#[tokio::main(flavor = "multi_thread", worker_threads = 2)]
async fn main() -> Result<(), Box<dyn std::error::Error>> {
    tracing_subscriber::fmt().init();

    let key = std::env::var("PROCTOR_SECRET")?;
    let secret = Secret::new(&key).ok_or("PROCTOR_SECRET must be at least 32 characters")?;
    let bind = std::env::var("PROCTOR_BIND").unwrap_or_else(|_| "127.0.0.1:8090".into());
    let redis_url = std::env::var("PROCTOR_REDIS_URL").unwrap_or_else(|_| "redis://127.0.0.1:6379/0".into());
    let max_connections = std::env::var("PROCTOR_MAX_CONNECTIONS")
        .ok()
        .and_then(|v| v.parse().ok())
        .unwrap_or(12_000);

    let (events, _) = broadcast::channel(BROADCAST_BACKLOG);
    tokio::spawn(relay_redis(redis_url, events.clone()));

    let state = AppState {
        secret: Arc::new(secret),
        events,
        connections: Arc::new(AtomicUsize::new(0)),
        max_connections,
    };

    let app = Router::new()
        .route("/ws", get(ws_handler))
        .route("/healthz", get(|| async { "ok" }))
        .with_state(state);

    let listener = tokio::net::TcpListener::bind(&bind).await?;
    info!("proctor listening on {bind}");
    axum::serve(listener, app).await?;
    Ok(())
}

async fn relay_redis(url: String, tx: broadcast::Sender<Arc<Event>>) {
    loop {
        if let Err(e) = relay_once(&url, &tx).await {
            warn!("redis relay stopped: {e}; retrying in 2s");
        }
        tokio::time::sleep(Duration::from_secs(2)).await;
    }
}

async fn relay_once(url: &str, tx: &broadcast::Sender<Arc<Event>>) -> redis::RedisResult<()> {
    let client = redis::Client::open(url)?;
    let mut pubsub = client.get_async_pubsub().await?;
    pubsub.subscribe(CHANNEL).await?;
    info!("subscribed to {CHANNEL}");

    let mut messages = std::pin::pin!(pubsub.on_message());
    while let Some(msg) = messages.next().await {
        let Ok(raw) = msg.get_payload::<String>() else { continue };
        if let Some(event) = Event::parse(raw) {
            // no connected clients is not an error
            let _ = tx.send(Arc::new(event));
        }
    }
    Ok(())
}

async fn ws_handler(
    ws: WebSocketUpgrade,
    Query(query): Query<WsQuery>,
    State(state): State<AppState>,
) -> impl IntoResponse {
    let Ok(claims) = state.secret.verify(&query.token, now_unix()) else {
        return StatusCode::UNAUTHORIZED.into_response();
    };
    // Reserve a slot for the whole handshake attempt, not just the live
    // session. axum 0.8 never invokes the on_upgrade callback when the
    // handshake fails or is abandoned mid-flight (a client that stalls or
    // vanishes before finishing the upgrade) — it drops the never-called
    // callback instead. ConnSlot moves into the callback, so the slot is
    // released on every outcome instead of leaking until restart; repeated
    // abandoned handshakes can no longer exhaust PROCTOR_MAX_CONNECTIONS.
    let Some(slot) = ConnSlot::reserve(&state) else {
        return StatusCode::SERVICE_UNAVAILABLE.into_response();
    };

    let rx = state.events.subscribe();
    ws.max_message_size(MAX_FRAME_BYTES)
        .read_buffer_size(SOCKET_BUFFER_BYTES)
        .write_buffer_size(SOCKET_BUFFER_BYTES)
        .on_failed_upgrade(|e| warn!("ws handshake failed: {e}"))
        .on_upgrade(move |socket| async move {
            serve(socket, claims, rx).await;
            // session over: release the reserved slot
            drop(slot);
        })
        .into_response()
}

/// One reserved WS connection slot.
///
/// Invariant: every handshake attempt that reserves a slot releases it
/// exactly once, whatever the outcome. `ws_handler` moves the guard into the
/// `on_upgrade` callback: on a completed handshake the callback runs and the
/// guard is dropped when the session future finishes; on a failed or
/// abandoned handshake axum drops the uncalled callback, dropping the guard
/// with it. The decrement therefore happens exactly once either way.
struct ConnSlot(Arc<AtomicUsize>);

impl ConnSlot {
    /// Reserve one slot, or `None` when the service is at its connection
    /// limit (the 503 path in `ws_handler`).
    fn reserve(state: &AppState) -> Option<Self> {
        if state.connections.fetch_add(1, Ordering::AcqRel) >= state.max_connections {
            state.connections.fetch_sub(1, Ordering::AcqRel);
            return None;
        }
        Some(Self(state.connections.clone()))
    }
}

impl Drop for ConnSlot {
    fn drop(&mut self) {
        self.0.fetch_sub(1, Ordering::AcqRel);
    }
}

async fn serve(socket: WebSocket, claims: Claims, mut rx: broadcast::Receiver<Arc<Event>>) {
    let (mut tx, mut inbound) = socket.split();
    let mut ping = tokio::time::interval(PING_EVERY);

    loop {
        tokio::select! {
            event = rx.recv() => match event {
                Ok(e) if claims.covers(e.attempt_id, e.assessment_id, e.user_id, e.school_id, e.students_only) => {
                    if tx.send(Message::Text(e.raw.clone().into())).await.is_err() {
                        break;
                    }
                }
                Ok(_) => {}
                // this client fell behind; it re-syncs from the Laravel snapshot
                Err(broadcast::error::RecvError::Lagged(_)) => {
                    if tx.send(Message::Text(r#"{"type":"resync"}"#.into())).await.is_err() {
                        break;
                    }
                }
                Err(broadcast::error::RecvError::Closed) => break,
            },
            _ = ping.tick() => {
                if claims.expired(now_unix()) || tx.send(Message::Ping(Default::default())).await.is_err() {
                    break;
                }
            }
            frame = inbound.next() => match frame {
                None | Some(Err(_)) | Some(Ok(Message::Close(_))) => break,
                Some(Ok(_)) => {}
            },
        }
    }
}

fn now_unix() -> u64 {
    SystemTime::now().duration_since(UNIX_EPOCH).map(|d| d.as_secs()).unwrap_or(0)
}

#[cfg(test)]
mod tests {
    use super::*;

    // A full end-to-end "abandoned handshake" test is not practical here: the
    // leak depends on axum/hyper dropping the upgrade task mid-flight, which
    // needs a real socket and cannot be driven deterministically offline. The
    // tests below pin the ConnSlot mechanics that make the handler leak-free:
    // the slot is released both when the callback runs to completion and when
    // the callback is dropped uncalled (axum's failed/abandoned-handshake
    // path), and the 503-at-limit path does not itself hold a slot.

    fn test_state(max: usize) -> (AppState, Arc<AtomicUsize>) {
        let connections = Arc::new(AtomicUsize::new(0));
        let (events, _) = broadcast::channel(BROADCAST_BACKLOG);
        let state = AppState {
            secret: Arc::new(Secret::new("0123456789abcdef0123456789abcdef").unwrap()),
            events,
            max_connections: max,
            connections: connections.clone(),
        };
        (state, connections)
    }

    #[test]
    fn reserve_refuses_at_capacity_without_leaking_a_slot() {
        let (state, connections) = test_state(1);

        let first = ConnSlot::reserve(&state);
        assert!(first.is_some(), "first client gets the only slot");
        assert_eq!(connections.load(Ordering::Acquire), 1);

        // at the limit the next attempt is refused (ws_handler returns 503)
        // and must not leave a slot behind
        assert!(ConnSlot::reserve(&state).is_none());
        assert_eq!(connections.load(Ordering::Acquire), 1, "refused reservation leaked a slot");

        drop(first);
        assert!(ConnSlot::reserve(&state).is_some(), "released slot is reusable");
    }

    #[test]
    fn abandoned_handshake_releases_its_slot() {
        // axum 0.8 drops the never-invoked on_upgrade callback when the
        // handshake fails or is abandoned; the ConnSlot captured by the
        // closure must be released by that drop.
        let connections = Arc::new(AtomicUsize::new(0));
        connections.fetch_add(1, Ordering::AcqRel); // ws_handler reserved a slot

        let on_upgrade = {
            let slot = ConnSlot(connections.clone());
            move |_socket: ()| async move { drop(slot) }
        };
        assert_eq!(connections.load(Ordering::Acquire), 1);

        drop(on_upgrade); // handshake abandoned: callback dropped uncalled
        assert_eq!(connections.load(Ordering::Acquire), 0, "abandoned handshake leaked its slot");
    }

    #[test]
    fn slot_is_held_for_the_session_and_released_afterwards() {
        let connections = Arc::new(AtomicUsize::new(0));
        connections.fetch_add(1, Ordering::AcqRel);

        let on_upgrade = {
            let slot = ConnSlot(connections.clone());
            move |_socket: ()| async move { drop(slot) }
        };
        let session = on_upgrade(());
        assert_eq!(connections.load(Ordering::Acquire), 1, "slot must be held while the session runs");

        tokio::runtime::Builder::new_current_thread()
            .build()
            .unwrap()
            .block_on(session);
        assert_eq!(connections.load(Ordering::Acquire), 0, "finished session must release its slot");
    }
}
