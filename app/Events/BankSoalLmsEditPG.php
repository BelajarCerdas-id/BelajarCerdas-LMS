<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class BankSoalLmsEditPG
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

    public function broadcastAs(): string
    {
        return 'edit.question.bank.pg';
    }
}
