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
    if state.connections.fetch_add(1, Ordering::AcqRel) >= state.max_connections {
        state.connections.fetch_sub(1, Ordering::AcqRel);
        return StatusCode::SERVICE_UNAVAILABLE.into_response();
    }

    let rx = state.events.subscribe();
    let connections = state.connections.clone();
    ws.max_message_size(MAX_FRAME_BYTES)
        .read_buffer_size(SOCKET_BUFFER_BYTES)
        .write_buffer_size(SOCKET_BUFFER_BYTES)
        .on_upgrade(move |socket| async move {
            serve(socket, claims, rx).await;
            connections.fetch_sub(1, Ordering::AcqRel);
        })
        .into_response()
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
