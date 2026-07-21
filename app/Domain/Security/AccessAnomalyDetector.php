<?Php

namespace App\Services\Security;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AccessAnomalyDetector
{
    public function tooManyAccesses(int $limit = 100, int $minutes = 5): bool
    {
        $userId = Auth::id();

        if (!$userId) {
            return false;
        }

        return AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count() > $limit;
    }
}