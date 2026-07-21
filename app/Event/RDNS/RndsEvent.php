<?php

namespace App\Events\RNDS;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RndsEvent implements ShouldBroadcast
{
    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('rnds-stream'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'rnds.event';
    }
}