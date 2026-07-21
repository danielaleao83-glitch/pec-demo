<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Storage;

use RuntimeException;
use Throwable;

class SisabStorageException extends RuntimeException
{
    /**
     * Código padrão SISAB para falhas de storage
     */
    public const CODE_STORAGE_FAILURE = 7001;

    /**
     * Criação segura da exceção com normalização de contexto
     */
    public static function from(
        Throwable $e,
        array $context = []
    ): self {

        return new self(
            message: self::buildMessage($e, $context),
            code: self::normalizeCode($e),
            previous: $e
        );
    }

    /**
     * 🔐 Mensagem controlada (evita vazamento sensível)
     */
    private static function buildMessage(
        Throwable $e,
        array $context
    ): string {
        return 'Falha no storage SISAB';
    }

    /**
     * 🔐 Normalização de código (evita instabilidade entre drivers)
     */
    private static function normalizeCode(Throwable $e): int
    {
        $code = (int) $e->getCode();

        if ($code <= 0) {
            return self::CODE_STORAGE_FAILURE;
        }

        return $code;
    }
}