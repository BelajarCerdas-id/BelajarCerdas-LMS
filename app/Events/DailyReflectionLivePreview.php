<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DailyReflectionLivePreview implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $tipe_model; // db SchReflAnswer
    public $action;
    public $data;
    public function __construct($tipe_model, $action, $data)
    {
        $this->tipe_model = $tipe_model;
        $this->action = $action;
        $this->data = $data;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
        return new Channel('dailyReflectionLivePreview');
    }

    public function broadcastAs(): string
    {
        return 'daily.reflection.live-preview';
    }

    public function broadcastWith(): array
    {
        return [
            'tipe_model' => $this->tipe_model,
            'action' => $this->action,
            'data' => $this->data,
        ];
    }
}