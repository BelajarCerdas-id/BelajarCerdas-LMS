<?php

namespace App\Events;

use App\Models\LmsQuestionBank;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BankSoalLmsUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $bankSoal;
    public function __construct(LmsQuestionBank $bankSoal)
    {
        $this->bankSoal = $bankSoal;
    }

    public function broadcastAs(): string
    {
        return 'bulk.upload.soal.lms';
    }
}
