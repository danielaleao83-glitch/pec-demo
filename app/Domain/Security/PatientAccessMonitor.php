<?Php

namespace App\Services\Security;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class PatientAccessMonitor
{
    public function exceededLimit(int $limit = 60, int $minutes = 10): bool
    {
        $userId = Auth::id();

        if (!$userId) {
            return false;
        }

        return AuditLog::where('user_id', $userId)
            ->where('acao', 'visualizar_paciente')
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count() > $limit;
    }
}