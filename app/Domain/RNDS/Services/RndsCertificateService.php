<?php

namespace App\Services\ESusService\RNDS;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;
use RuntimeException;

class RndsCertificateService
{
    protected string $certPath;
    protected string $certPassword;

    public function __construct()
    {
        $this->certPath = storage_path('app/certificados/rnds.pfx');
        $this->certPassword = config('services.rnds.cert_password');
    }

    // =========================================================
    // 🔐 ENTRYPOINT ABSOLUTO (FEDERAL GATEWAY)
    // =========================================================
    public function getCertificado(): array
    {
        $traceId = (string) Str::uuid();
        $context = $this->context();

        $this->guard($traceId, $context);
        $this->circuitBreaker($context);

        try {
            return Cache::lock('rnds:cert:vault:lock', 5)
                ->block(3, function () use ($traceId, $context) {

                    return Cache::remember(
                        $this->cacheKey($context),
                        600,
                        function () use ($traceId, $context) {

                            $this->validateIntegrity($traceId, $context);
                            $this->validateFileHardening($traceId);

                            $pkcs12 = file_get_contents($this->certPath);

                            if (!$pkcs12) {
                                $this->fail('CERT_READ_FAIL', $traceId, $context);
                                throw new RuntimeException('Falha leitura certificado');
                            }

                            if (!openssl_pkcs12_read($pkcs12, $certs, $this->certPassword)) {
                                $this->fail('CERT_DECRYPT_FAIL', $traceId, $context);
                                throw new RuntimeException('Falha decrypt RNDS');
                            }

                            $hash = hash('sha256', $pkcs12 . $traceId);

                            $this->audit($traceId, $context, $hash);

                            return [
                                'cert' => $certs['cert'],
                                'pkey' => $certs['pkey'],
                                'hash' => $hash,
                                'trace_id' => $traceId,
                                'fingerprint' => $context['fingerprint'],
                            ];
                        }
                    );
                });

        } catch (Throwable $e) {
            $this->fail('CERT_EXCEPTION', $traceId, $context, $e->getMessage());
            throw $e;
        }
    }

    // =========================================================
    // 🧠 CONTEXTO FEDERAL (IDENTIDADE SEGURA)
    // =========================================================
    protected function context(): array
    {
        return [
            'ip' => request()->ip() ?? 'CLI',
            'ua' => substr(request()->userAgent() ?? '', 0, 120),
            'user_id' => auth()->id(),
            'fingerprint' => hash('sha256',
                (request()->ip() ?? '') .
                (request()->userAgent() ?? '') .
                (auth()->id() ?? 'guest')
            ),
        ];
    }

    protected function cacheKey(array $context): string
    {
        return 'rnds:cert:vault:' . $context['fingerprint'];
    }

    // =========================================================
    // 🚫 ANTI-ABUSE + REPLAY GLOBAL
    // =========================================================
    protected function guard(string $traceId, array $context): void
    {
        $key = "rnds:rate:{$context['fingerprint']}";

        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->fail('RATE_LIMIT_BLOCK', $traceId, $context);
            throw new RuntimeException('Rate limit bloqueado RNDS');
        }

        RateLimiter::hit($key, 60);

        $replayKey = "rnds:replay:{$context['fingerprint']}";

        if (Cache::has($replayKey)) {
            $this->fail('REPLAY_DETECTED', $traceId, $context);
            throw new RuntimeException('Replay detectado');
        }

        Cache::put($replayKey, $traceId, 300);
    }

    // =========================================================
    // 🧱 CIRCUIT BREAKER REAL
    // =========================================================
    protected function circuitBreaker(array $context): void
    {
        $key = "rnds:failures:{$context['fingerprint']}";

        if (Cache::get($key, 0) > 5) {
            throw new RuntimeException('Circuit breaker ativo RNDS');
        }
    }

    // =========================================================
    // 🔐 INTEGRIDADE DO ARQUIVO (ANTI TAMPER)
    // =========================================================
    protected function validateIntegrity(string $traceId, array $context): void
    {
        if (!file_exists($this->certPath)) {
            $this->fail('CERT_NOT_FOUND', $traceId, $context);
            throw new RuntimeException('Certificado não encontrado');
        }

        $current = hash_file('sha256', $this->certPath);

        $baseline = Cache::rememberForever('rnds:cert:baseline_hash', fn () => $current);

        if ($current !== $baseline) {
            $this->fail('CERT_TAMPER', $traceId, $context);
            throw new RuntimeException('INTEGRIDADE COMPROMETIDA');
        }
    }

    // =========================================================
    // 🔒 HARDENING DO ARQUIVO
    // =========================================================
    protected function validateFileHardening(string $traceId): void
    {
        if (!is_readable($this->certPath)) {
            throw new RuntimeException('Arquivo inacessível RNDS');
        }
    }

    // =========================================================
    // 🧾 AUDITORIA ENCadeada (FEDERAL STYLE)
    // =========================================================
    protected function audit(string $traceId, array $context, string $hash): void
    {
        $prev = Cache::get('rnds:audit:last');

        $chain = hash('sha256', $hash . $prev . $traceId);

        Log::channel('audit')->info('CERT_ACCESS', [
            'trace_id' => $traceId,
            'hash' => $hash,
            'chain' => $chain,
            'context' => $context,
            'time' => now()->toIso8601String(),
        ]);

        Cache::put('rnds:audit:last', $chain, 3600);
    }

    // =========================================================
    // 🚨 FAIL LOG FEDERAL
    // =========================================================
    protected function fail(string $event, string $traceId, array $context, ?string $error = null): void
    {
        Log::channel('security')->critical($event, [
            'trace_id' => $traceId,
            'context' => $context,
            'error' => $error,
            'time' => now()->toIso8601String(),
        ]);

        $key = "rnds:failures:{$context['fingerprint']}";
        Cache::increment($key);
        Cache::put($key, Cache::get($key), 300);
    }
}