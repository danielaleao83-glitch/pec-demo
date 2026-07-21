<?php

namespace App\Models\Comunicacao;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    use HasUuids;

    protected $table = 'whatsapp_messages';

    /*
    |--------------------------------------------------------------------------
    | 🧱 CAMPOS CONTROLADOS
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'phone',
        'message',

        'paciente_id',
        'user_id',

        'status',
        'attempts',
        'max_attempts',
        'next_retry_at',

        'response',
        'error',

        'idempotency_key',

        // 🆕 produção real
        'sent_at',
        'context',
        'provider',
        'channel',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔄 CASTS
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'response' => 'array',
        'next_retry_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | 📊 STATUS PADRÃO (CONSISTÊNCIA HOSPITALAR)
    |--------------------------------------------------------------------------
    */
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    /*
    |--------------------------------------------------------------------------
    | 🔎 SCOPES (FILA INTELIGENTE)
    |--------------------------------------------------------------------------
    */

    // mensagens aguardando envio
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // mensagens que podem ser reprocessadas
    public function scopeReadyToRetry($query)
    {
        return $query->where('status', self::STATUS_FAILED)
            ->where(function ($q) {
                $q->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            })
            ->whereColumn('attempts', '<', 'max_attempts');
    }

    // mensagens em processamento
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /*
    |--------------------------------------------------------------------------
    | 🧠 HELPERS DE ESTADO
    |--------------------------------------------------------------------------
    */

    public function canRetry(): bool
    {
        return $this->attempts < $this->max_attempts;
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
        ]);
    }

    public function markAsSent(array $response = []): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'response' => $response,
        ]);
    }

    public function markAsFailed(string $error, ?int $delayMinutes = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error' => $error,
            'attempts' => $this->attempts + 1,
            'next_retry_at' => $delayMinutes
                ? now()->addMinutes($delayMinutes)
                : now()->addMinutes(5),
        ]);
    }
}
