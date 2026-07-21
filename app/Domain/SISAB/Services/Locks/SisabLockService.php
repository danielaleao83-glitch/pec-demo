<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Locks;

use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class SisabLockService
{
    /**
     * 🔐 TTL do lock distribuído
     */
    private const LOCK_TTL = 10;

    /**
     * 🚀 execução segura com lock distribuído
     */
    public static function executar(string $fingerprint, callable $callback)
    {
        $key = self::buildKey($fingerprint);

        /**
         * 🔒 lock distribuído (anti race condition hospitalar)
         */
        $lock = Cache::lock($key, self::LOCK_TTL);

        if (!$lock->get()) {
            throw new RuntimeException('SISAB LOCK: concorrência detectada');
        }

        try {

            /**
             * 🧠 execução protegida
             */
            return $callback();

        } catch (Throwable $e) {

            throw new RuntimeException(
                'SISAB LOCK FAILURE: ' . $e->getMessage(),
                0,
                $e
            );

        } finally {

            optional($lock)->release();
        }
    }

    /**
     * 🔐 fingerprint isolado por ambiente
     */
    private static function buildKey(string $fingerprint): string
    {
        return sprintf(
            'sisab:lock:%s:%s',
            app()->environment(),
            hash('sha256', $fingerprint)
        );
    }

    /**
     * 🔥 fingerprint base (opcional helper de segurança)
     */
    public static function fingerprint(array $data): string
    {
        return hash('sha256', json_encode($data));
    }
}