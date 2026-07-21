<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;
use Illuminate\Log\Logger;
use Throwable;

class CustomizeFormatter
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {

            // 📦 Formatter JSON (com stack trace)
            $handler->setFormatter(
                new JsonFormatter(
                    JsonFormatter::BATCH_MODE_JSON,
                    true // 🔥 inclui stack trace automaticamente
                )
            );

            // 🔄 Processor seguro (Monolog v3)
            $handler->pushProcessor(function (LogRecord $record) {

                try {
                    $context = $this->sanitize($record->context);
                    $extra = $record->extra;

                    // 📡 Origem
                    if (app()->runningInConsole()) {
                        $extra['source'] = 'console';
                    } else {
                        $extra['correlation_id'] = request()->header('X-Correlation-ID') ?? null;
                        $extra['user_id'] = auth()->check() ? auth()->id() : null;
                        $extra['ip'] = request()->ip() ?? 'unknown';
                        $extra['route'] = request()->route()?->getName();
                        $extra['url'] = request()->fullUrl();
                        $extra['method'] = request()->method();
                    }

                    $extra['timestamp_iso'] = now()->toISOString();

                    return $record->with(
                        context: $context,
                        extra: $extra
                    );

                } catch (Throwable $e) {
                    return $record; // fail-safe
                }
            });
        }
    }

    /**
     * 🔐 Sanitização LGPD
     */
    protected function sanitize(array $data): array
    {
        $sensibles = [
            'cpf',
            'cns',
            'senha',
            'password',
            'token',
            'access_token',
            'refresh_token',
            'authorization',
            'cartao_sus'
        ];

        foreach ($data as $key => $value) {

            if (in_array(strtolower($key), $sensibles)) {
                $data[$key] = '***PROTEGIDO***';
                continue;
            }

            if (is_string($value) && strlen($value) > 1000) {
                $data[$key] = substr($value, 0, 1000) . '...';
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }

        return $data;
    }
}