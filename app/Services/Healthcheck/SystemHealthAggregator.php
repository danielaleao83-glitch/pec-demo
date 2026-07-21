<?php

declare(strict_types=1);

namespace App\Services\Healthcheck;

class SystemHealthAggregator
{
    public function __construct(
        private DatabaseHealthcheck $db,
        private CacheHealthcheck $cache,
        private QueueHealthcheck $queue,
        private ExternalApisHealthcheck $external
    ) {}

    public function check(): array
    {
        return [
            'database' => $this->db->check(),
            'cache' => $this->cache->check(),
            'queue' => $this->queue->check(),
            'external' => $this->external->check(),

            'overall_status' => $this->resolveStatus([
                $this->db->check()['status'],
                $this->cache->check()['status'],
                $this->queue->check()['status'],
                $this->external->check()['status'],
            ]),
        ];
    }

    private function resolveStatus(array $statuses): string
    {
        return in_array('down', $statuses, true)
            ? 'degraded'
            : 'healthy';
    }
}