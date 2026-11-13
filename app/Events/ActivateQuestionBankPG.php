<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class ActivateQuestionBankPG
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $source;
    public $status;
    public $affectedRows;
    public function __construct($source, $status, $affectedRows)
    {
        $this->source = $source;
        $this->status = $status;
        $this->affectedRows = $affectedRows;
    }

    public function broadcastAs(): string
    {
        return 'activate.question.bank.pg';
    }
}
