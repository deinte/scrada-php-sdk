<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

/**
 * Response from creating a sales invoice.
 */
final readonly class CreateSalesInvoiceResponse
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $id,
        public string $status,
        public array $raw = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: is_string($data['id'] ?? null) ? $data['id'] : '',
            status: is_string($data['status'] ?? null) ? $data['status'] : 'draft',
            raw: $data
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->raw !== [] ? $this->raw : [
            'id' => $this->id,
            'status' => $this->status,
        ];
    }
}
