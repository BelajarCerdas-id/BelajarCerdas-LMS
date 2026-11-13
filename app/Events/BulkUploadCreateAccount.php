<?php

namespace App\Events;

use App\Models\UserAccount;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulkUploadCreateAccount
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $createAccounts;
    public function __construct(UserAccount $createAccounts)
    {
        $this->createAccounts = $createAccounts;
    }

    public function broadcastAs(): string
    {
        return 'bulk.upload.create.account';
    }
}
