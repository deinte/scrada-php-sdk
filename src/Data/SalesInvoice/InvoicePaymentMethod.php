<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\SalesInvoice;

/**
 * Represents a payment method for a sales invoice.
 *
 * Used to mark invoices as (partially) paid at creation time.
 */
final readonly class InvoicePaymentMethod
{
    public function __construct(
        public int $paymentType,
        public string $name,
        public ?float $totalPaid = null,
        public ?float $totalToPay = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $paymentType = $data['paymentType'] ?? 0;

        return new self(
            paymentType: is_int($paymentType) ? $paymentType : (is_numeric($paymentType) ? (int) $paymentType : 0),
            name: is_string($data['name'] ?? null) ? $data['name'] : '',
            totalPaid: isset($data['totalPaid']) && is_numeric($data['totalPaid']) ? (float) $data['totalPaid'] : null,
            totalToPay: isset($data['totalToPay']) && is_numeric($data['totalToPay']) ? (float) $data['totalToPay'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'paymentType' => $this->paymentType,
            'name' => $this->name,
        ];

        if ($this->totalPaid !== null) {
            $payload['totalPaid'] = round($this->totalPaid, 2);
        }

        if ($this->totalToPay !== null) {
            $payload['totalToPay'] = round($this->totalToPay, 2);
        }

        return $payload;
    }
}
