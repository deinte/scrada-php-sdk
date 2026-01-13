<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\SalesInvoice;

use Deinte\ScradaSdk\Enums\PaymentType;

/**
 * Represents a payment method for a sales invoice.
 *
 * Used to mark invoices as (partially) paid at creation time.
 */
final readonly class InvoicePaymentMethod
{
    public function __construct(
        public PaymentType $paymentType,
        public string $name,
        public ?float $totalPaid = null,
        public ?float $totalToPay = null,
        public ?string $paymentReference = null,
    ) {}

    /**
     * Create from PaymentType enum with automatic name.
     */
    public static function fromType(
        PaymentType $type,
        ?float $totalPaid = null,
        ?float $totalToPay = null,
        ?string $paymentReference = null,
    ): self {
        return new self(
            paymentType: $type,
            name: $type->label(),
            totalPaid: $totalPaid,
            totalToPay: $totalToPay,
            paymentReference: $paymentReference,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $paymentTypeValue = $data['paymentType'] ?? 1;
        $paymentTypeInt = is_int($paymentTypeValue) ? $paymentTypeValue : (is_numeric($paymentTypeValue) ? (int) $paymentTypeValue : 1);
        $paymentType = PaymentType::tryFrom($paymentTypeInt) ?? PaymentType::BankTransfer;

        return new self(
            paymentType: $paymentType,
            name: is_string($data['name'] ?? null) ? $data['name'] : $paymentType->label(),
            totalPaid: isset($data['totalPaid']) && is_numeric($data['totalPaid']) ? (float) $data['totalPaid'] : null,
            totalToPay: isset($data['totalToPay']) && is_numeric($data['totalToPay']) ? (float) $data['totalToPay'] : null,
            paymentReference: isset($data['paymentReference']) && is_string($data['paymentReference']) ? $data['paymentReference'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'paymentType' => $this->paymentType->value,
            'name' => $this->name,
        ];

        if ($this->totalPaid !== null) {
            $payload['totalPaid'] = round($this->totalPaid, 2);
        }

        if ($this->totalToPay !== null) {
            $payload['totalToPay'] = round($this->totalToPay, 2);
        }

        if ($this->paymentReference !== null) {
            $payload['paymentReference'] = $this->paymentReference;
        }

        return $payload;
    }
}
