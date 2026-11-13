<?php

namespace App\Events;

use App\Models\SchContract;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LmsSchoolSubscription
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $subscription;
    public function __construct(SchContract $subscription)
    {
        $this->subscription = $subscription;
    }

    public function broadcastAs(): string
    {
        return 'management.school.subscription';
    }
}
