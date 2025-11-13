<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

/**
 * Describes how a sales invoice has been dispatched.
 */
final readonly class SendStatus
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $status,
        public bool $peppolSent,
        public bool $emailSent,
        public bool $pending,
        public array $meta = [],
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            status: is_string($data['status'] ?? null) ? $data['status'] : '',
            peppolSent: (bool) ($data['peppolSent'] ?? ($data['peppol'] ?? false)),
            emailSent: (bool) ($data['emailSent'] ?? false),
            pending: (bool) ($data['pending'] ?? false),
            meta: $data
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->meta !== [] ? $this->meta : [
            'status' => $this->status,
            'peppolSent' => $this->peppolSent,
            'emailSent' => $this->emailSent,
            'pending' => $this->pending,
        ];
    }
}
