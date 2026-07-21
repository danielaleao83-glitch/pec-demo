<?php

namespace App\Services\Core;

use App\Services\Core\Event\SusEventDispatcherService;
use App\Services\Core\Security\SusSecurityService;

class CoreEngine
{
    public function __construct(
        protected SusEventDispatcherService $event,
        protected SusSecurityService $security
    ) {}

    /**
     * 🚀 EXECUÇÃO CENTRAL SUS (PADRÃO FEDERAL)
     */
    public function execute(string $action, array $payload = []): array
    {
        // 🔐 segurança base
        $fingerprint = $this->security->fingerprint();

        // 📡 evento global
        $eventId = $this->event->dispatch($action, [
            'payload' => $payload,
            'fingerprint' => $fingerprint,
        ]);

        // 🧾 auditoria central
        $this->security->audit($action, [
            'event_id' => $eventId,
            'payload' => $payload,
        ]);

        return [
            'status' => 'OK',
            'event_id' => $eventId,
            'fingerprint' => $fingerprint,
        ];
    }
}