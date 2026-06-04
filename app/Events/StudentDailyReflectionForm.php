<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentDailyReflectionForm implements ShouldBroadcastNow
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
        return new Channel('studentDailyReflectionForm');
    }

    public function broadcastAs(): string
    {
        return 'student.daily.reflection.form';
    }
}