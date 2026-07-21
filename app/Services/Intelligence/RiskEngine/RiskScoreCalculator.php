<?php

namespace App\Services\Intelligence\RiskEngine;

class RiskScoreCalculator
{
    public function calculate(array $data): int
    {
        $score = 0;

        $score += $data['auth_failures'] ?? 0 * 5;
        $score += $data['sensitive_routes'] ?? 0 * 10;
        $score += $data['sus_actions'] ?? 0 * 20;

        return min($score, 100);
    }
}