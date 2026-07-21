<?php

namespace App\Events\Dashboard;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RndsDashboardUpdated implements ShouldBroadcast
{
    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('rnds-dashboard'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'dashboard.updated';
    }
}