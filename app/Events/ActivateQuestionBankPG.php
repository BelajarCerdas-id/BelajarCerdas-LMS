<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class ActivateQuestionBankPG implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $subBabId;
    public $source;
    public $status;
    public $affectedRows;
    public function __construct($subBabId, $source, $status, $affectedRows)
    {
        $this->subBabId = $subBabId;
        $this->source = $source;
        $this->status = $status;
        $this->affectedRows = $affectedRows;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
        return new Channel('activateQuestionBankPG');
    }

    public function broadcastAs(): string
    {
        return 'activate.question.bank.pg';
    }
}
