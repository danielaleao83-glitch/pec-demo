<?php

namespace App\Services\Intelligence\RiskEngine;

class ThreatClassifier
{
    public function classify(array $analysis): string
    {
        return match ($analysis['level']) {
            'CRITICAL' => 'BLOCK_AND_ALERT',
            'HIGH'     => 'STEP_UP_AUTH',
            'MEDIUM'   => 'MONITOR',
            default    => 'ALLOW',
        };
    }
}