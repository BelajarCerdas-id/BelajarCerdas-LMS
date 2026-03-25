<?php

namespace App\Events;

use App\Models\LmsQuestionBank;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BankSoalLmsUploaded implements ShouldBroadcastNow
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

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
        return new Channel('bankSoalLmsUploaded');
    }

    public function broadcastAs(): string
    {
        return 'bulk.upload.soal.lms';
    }
}
