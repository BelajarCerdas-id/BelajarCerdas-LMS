<?php

namespace App\Events;

use App\Models\UserAccount;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LmsManagementAccount
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $userAccount;
    public function __construct(UserAccount $userAccount)
    {
        $this->userAccount = $userAccount;
    }

    public function broadcastAs(): string
    {
        return 'management.account';
    }
}
