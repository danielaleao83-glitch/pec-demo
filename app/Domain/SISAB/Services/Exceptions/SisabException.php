<?php

declare(strict_types=1);

namespace App\Services\ESusService\SISAB\Exceptions;

use Exception;
use Throwable;

class SisabException extends Exception
{
    /**
     * 🧠 contexto completo SISAB (auditável)
     */
    protected array $context = [];

    /**
     * 🔐 tipo de erro SISAB (classificação operacional)
     */
    protected string $type;

    public function __construct(
        string $message = 'Erro SISAB',
        int $code = 0,
        array $context = [],
        string $type = 'GENERIC_ERROR',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
        $this->type = $type;
    }

    /**
     * 🧾 contexto estruturado (auditoria + logs)
     */
    public function context(): array
    {
        return $this->context;
    }

    /**
     * 🔐 tipo do erro (classificação SISAB)
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * 🚀 serialização segura para logs
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'type' => $this->type,
            'context' => $this->context,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];
    }
}