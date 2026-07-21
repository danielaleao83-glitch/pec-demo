<?php

declare(strict_types=1);

namespace App\Domain\Atendimento\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

/**
 * =========================================================
 * 🏥 STATUS ATENDIMENTO (FEDERAL GRADE)
 * =========================================================
 *
 * ✔ Imutável
 * ✔ Determinístico
 * ✔ Auditável
 * ✔ Replay-safe
 * ✔ Event-sourcing ready
 * ✔ Máquina de estados compatível
 * ✔ Zero-trust input
 * ✔ Hash estável
 * ✔ Compatível RNDS/e-SUS
 * ✔ Anti-corrupção de domínio
 *
 * =========================================================
 */
final class StatusAtendimento implements JsonSerializable
{
    // =========================================================
    // 🧠 ESTADOS OFICIAIS
    // =========================================================
    private const AGUARDANDO = 'aguardando';

    private const CHAMADO = 'chamado';

    private const EM_ATENDIMENTO = 'em_atendimento';

    private const FINALIZADO = 'finalizado';

    private const CANCELADO = 'cancelado';

    // =========================================================
    // 🔐 TODOS OS ESTADOS
    // =========================================================
    private const ALL = [

        self::AGUARDANDO,

        self::CHAMADO,

        self::EM_ATENDIMENTO,

        self::FINALIZADO,

        self::CANCELADO,
    ];

    // =========================================================
    // 🔁 TRANSIÇÕES OFICIAIS
    // =========================================================
    private const TRANSITIONS = [

        self::AGUARDANDO => [

            self::CHAMADO,

            self::CANCELADO,
        ],

        self::CHAMADO => [

            self::EM_ATENDIMENTO,

            self::CANCELADO,
        ],

        self::EM_ATENDIMENTO => [

            self::FINALIZADO,

            self::CANCELADO,
        ],

        self::FINALIZADO => [],

        self::CANCELADO => [],
    ];

    // =========================================================
    // 🔐 ESTADO INTERNO
    // =========================================================
    private readonly string $status;

    // =========================================================
    // 🔐 CONSTRUTOR PRIVADO
    // =========================================================
    private function __construct(
        string $status
    ) {

        $status = self::normalizeInput($status);

        self::ensureValid($status);

        $this->status = $status;
    }

    // =========================================================
    // 🟣 FACTORIES SEGURAS
    // =========================================================
    public static function aguardando(): self
    {
        return new self(self::AGUARDANDO);
    }

    public static function chamado(): self
    {
        return new self(self::CHAMADO);
    }

    public static function emAtendimento(): self
    {
        return new self(self::EM_ATENDIMENTO);
    }

    public static function finalizado(): self
    {
        return new self(self::FINALIZADO);
    }

    public static function cancelado(): self
    {
        return new self(self::CANCELADO);
    }

    // =========================================================
    // 🔐 RECONSTRUÇÃO SEGURA
    // =========================================================
    public static function fromString(
        string $status
    ): self {
        return new self($status);
    }

    // =========================================================
    // 🌐 INPUT EXTERNO (SUS/RNDS/SOAP)
    // =========================================================
    public static function fromExternal(
        string $status
    ): self {

        $status = preg_replace(
            '/[^a-zA-Z\_]/',
            '',
            $status
        );

        return new self($status);
    }

    // =========================================================
    // 🔐 VALIDAÇÃO CENTRALIZADA
    // =========================================================
    private static function ensureValid(
        string $status
    ): void {

        if ($status === '') {
            throw new InvalidArgumentException(
                'StatusAtendimento vazio'
            );
        }

        if (!in_array(
            $status,
            self::ALL,
            true
        )) {
            throw new InvalidArgumentException(
                "StatusAtendimento inválido: {$status}"
            );
        }
    }

    // =========================================================
    // 🧹 NORMALIZAÇÃO
    // =========================================================
    private static function normalizeInput(
        string $status
    ): string {

        return strtolower(
            trim($status)
        );
    }

    // =========================================================
    // 🔐 IDENTIDADE SEGURA
    // =========================================================
    public function equals(
        self $other
    ): bool {

        return hash_equals(
            $this->status,
            $other->status
        );
    }

    // =========================================================
    // 🔁 TRANSIÇÃO SEGURA
    // =========================================================
    public function canTransitionTo(
        self $next
    ): bool {

        return in_array(
            $next->value(),
            self::TRANSITIONS[$this->status],
            true
        );
    }

    // =========================================================
    // 🚨 GARANTE TRANSIÇÃO
    // =========================================================
    public function ensureCanTransitionTo(
        self $next
    ): void {

        if (!$this->canTransitionTo($next)) {

            throw new InvalidArgumentException(
                sprintf(
                    'Transição inválida [%s -> %s]',
                    $this->status,
                    $next->value()
                )
            );
        }
    }

    // =========================================================
    // 📌 VALOR PURO
    // =========================================================
    public function value(): string
    {
        return $this->status;
    }

    // =========================================================
    // 🔐 HASH ESTÁVEL
    // =========================================================
    public function hash(): string
    {
        return hash(
            'sha256',
            implode('|', [

                'STATUS_ATENDIMENTO',

                $this->status,
            ])
        );
    }

    // =========================================================
    // 🔗 FINGERPRINT
    // =========================================================
    public function fingerprint(): string
    {
        return substr(
            $this->hash(),
            0,
            16
        );
    }

    // =========================================================
    // 🧠 CONSULTAS SEMÂNTICAS
    // =========================================================
    public function isAguardando(): bool
    {
        return $this->status === self::AGUARDANDO;
    }

    public function isChamado(): bool
    {
        return $this->status === self::CHAMADO;
    }

    public function isEmAtendimento(): bool
    {
        return $this->status === self::EM_ATENDIMENTO;
    }

    public function isFinalizado(): bool
    {
        return $this->status === self::FINALIZADO;
    }

    public function isCancelado(): bool
    {
        return $this->status === self::CANCELADO;
    }

    // =========================================================
    // 🔐 ESTADO TERMINAL
    // =========================================================
    public function isTerminal(): bool
    {
        return in_array(
            $this->status,
            [

                self::FINALIZADO,

                self::CANCELADO,
            ],
            true
        );
    }

    // =========================================================
    // 🧠 ESTADO ATIVO
    // =========================================================
    public function isActive(): bool
    {
        return !$this->isTerminal();
    }

    // =========================================================
    // 🔐 ENUMERAÇÃO SEGURA
    // =========================================================
    public static function all(): array
    {
        return self::ALL;
    }

    // =========================================================
    // 🔁 TRANSIÇÕES DISPONÍVEIS
    // =========================================================
    public function allowedTransitions(): array
    {
        return self::TRANSITIONS[$this->status];
    }

    // =========================================================
    // 🧾 SERIALIZAÇÃO AUDITÁVEL
    // =========================================================
    public function toArray(): array
    {
        return [

            'status' => $this->status,

            'is_terminal' => $this->isTerminal(),

            'is_active' => $this->isActive(),

            'allowed_transitions'
                => $this->allowedTransitions(),

            'hash' => $this->hash(),

            'fingerprint'
                => $this->fingerprint(),
        ];
    }

    // =========================================================
    // 🔄 JSON SERIALIZABLE
    // =========================================================
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // =========================================================
    // 🔁 STRING CAST
    // =========================================================
    public function __toString(): string
    {
        return $this->status;
    }
}