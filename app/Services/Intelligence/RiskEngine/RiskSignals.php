<?php

namespace App\Services\Intelligence\RiskEngine;

class RiskSignals
{
    public const IP_ANOMALY = 'IP_ANOMALY';
    public const BRUTE_FORCE = 'BRUTE_FORCE';
    public const SUS_EXPORT = 'SUS_EXPORT';
    public const DATA_LEAK_ATTEMPT = 'DATA_LEAK_ATTEMPT';
}