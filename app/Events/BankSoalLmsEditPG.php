<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class BankSoalLmsEditPG implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $groupedSoal;
    public $questionId;

    public function __construct($groupedSoal, $questionId)
    {
        $this->groupedSoal = $groupedSoal;
        $this->questionId = $questionId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('editQuestionBankPG'), // Broadcast to review question
            new Channel('editQuestionBankPG.' . $this->questionId), // Broadcast to form edit question
        ];
    }

    public function broadcastAs(): string
    {
        return 'edit.question.bank.pg';
    }
}
