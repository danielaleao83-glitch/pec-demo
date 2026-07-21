<?php

namespace App\Services\Core\Event;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SusEventDispatcherService
{
    /**
     * 📡 EVENT BUS CENTRAL (RNDS STYLE)
     */
    public function dispatch(string $event, array $payload = []): string
    {
        $eventId = (string) Str::uuid();

        $envelope = [
            'event_id' => $eventId,
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'payload' => $payload,
            'hash' => $this->hash($event, $payload),
        ];

        Log::channel('audit')->info('SUS_EVENT', $envelope);

        return $eventId;
    }

    /**
     * 🔐 hash imutável do evento
     */
    protected function hash(string $event, array $payload): string
    {
        return hash('sha256', $event . '|' . json_encode($payload));
    }
}