<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data;

/**
 * Request data for creating a sales invoice.
 */
final readonly class CreateSalesInvoiceData
{
    /**
     * @param  array<int, InvoiceLine>  $lines
     * @param  array<int, Attachment>  $attachments
     */
    public function __construct(
        public string $bookYear,
        public string $journal,
        public string $number,
        public bool $creditInvoice,
        public string $invoiceDate,
        public string $invoiceExpiryDate,
        public float $totalInclVat,
        public float $totalExclVat,
        public float $totalVat,
        public Customer $customer,
        public array $lines,
        public bool $alreadySentToCustomer = false,
        public array $attachments = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $lines = array_map(
            static fn (array $line): InvoiceLine => InvoiceLine::fromArray($line),
            array_values(array_filter(
                array: is_array($data['lines'] ?? null) ? $data['lines'] : [],
                callback: static fn (mixed $line): bool => is_array($line)
            ))
        );

        $customerData = $data['customer'] ?? [];
        /** @var array<string, mixed> $customerArray */
        $customerArray = is_array($customerData) ? $customerData : [];

        // Parse attachments array
        $attachments = array_map(
            static fn (array $attachment): Attachment => Attachment::fromArray($attachment),
            array_values(array_filter(
                array: is_array($data['attachments'] ?? null) ? $data['attachments'] : [],
                callback: static fn (mixed $attachment): bool => is_array($attachment)
            ))
        );

        // Support legacy single PDF attachment format
        if (empty($attachments) && is_string($data['base64Data'] ?? null)) {
            $attachments = [
                Attachment::pdf(
                    filename: is_string($data['filename'] ?? null) ? $data['filename'] : 'invoice.pdf',
                    base64Data: $data['base64Data'],
                ),
            ];
        }

        return new self(
            bookYear: is_string($data['bookYear'] ?? null) ? $data['bookYear'] : '',
            journal: is_string($data['journal'] ?? null) ? $data['journal'] : '',
            number: is_string($data['number'] ?? null) ? $data['number'] : '',
            creditInvoice: (bool) ($data['creditInvoice'] ?? false),
            invoiceDate: is_string($data['invoiceDate'] ?? null) ? $data['invoiceDate'] : '',
            invoiceExpiryDate: is_string($data['invoiceExpiryDate'] ?? null) ? $data['invoiceExpiryDate'] : '',
            totalInclVat: is_float($data['totalInclVat'] ?? null) || is_int($data['totalInclVat'] ?? null) ? (float) $data['totalInclVat'] : 0.0,
            totalExclVat: is_float($data['totalExclVat'] ?? null) || is_int($data['totalExclVat'] ?? null) ? (float) $data['totalExclVat'] : 0.0,
            totalVat: is_float($data['totalVat'] ?? null) || is_int($data['totalVat'] ?? null) ? (float) $data['totalVat'] : 0.0,
            customer: Customer::fromArray($customerArray),
            lines: $lines,
            alreadySentToCustomer: (bool) ($data['alreadySendToCustomer'] ?? ($data['alreadySentToCustomer'] ?? false)),
            attachments: $attachments,
        );
    }

    /**
     * Check if any attachments are included.
     */
    public function hasAttachments(): bool
    {
        return count($this->attachments) > 0;
    }

    /**
     * Add an attachment.
     *
     * @return self New instance with added attachment
     */
    public function withAttachment(Attachment $attachment): self
    {
        return new self(
            bookYear: $this->bookYear,
            journal: $this->journal,
            number: $this->number,
            creditInvoice: $this->creditInvoice,
            invoiceDate: $this->invoiceDate,
            invoiceExpiryDate: $this->invoiceExpiryDate,
            totalInclVat: $this->totalInclVat,
            totalExclVat: $this->totalExclVat,
            totalVat: $this->totalVat,
            customer: $this->customer,
            lines: $this->lines,
            alreadySentToCustomer: $this->alreadySentToCustomer,
            attachments: [...$this->attachments, $attachment],
        );
    }

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $lines = array_map(
            static fn (InvoiceLine $line): array => $line->toArray(),
            $this->lines
        );

        // Build VAT totals grouped by VAT percentage (required by Scrada)
        $vatTotals = $this->buildVatTotals();

        $payload = [
            'bookYear' => $this->bookYear,
            'journal' => $this->journal,
            'number' => $this->number,
            'creditInvoice' => $this->creditInvoice,
            'invoiceDate' => $this->invoiceDate,
            'invoiceExpiryDate' => $this->invoiceExpiryDate,
            'customer' => $this->customer->toArray(),
            'totalInclVat' => round($this->totalInclVat, 2),
            'totalExclVat' => round($this->totalExclVat, 2),
            'totalVat' => round($this->totalVat, 2),
            'lines' => $lines,
            'vatTotals' => $vatTotals,
            'alreadySendToCustomer' => $this->alreadySentToCustomer,
        ];

        // Add attachments if present
        if ($this->hasAttachments()) {
            $payload['attachments'] = array_map(
                static fn (Attachment $attachment): array => $attachment->toArray(),
                $this->attachments
            );
        }

        return $payload;
    }

    /**
     * Build VAT totals grouped by VAT percentage.
     *
     * Scrada expects the following fields in vatTotals:
     * - vatType: int (1=21%, 2=12%, 3=6%, 4=0%)
     * - vatPercentage: float
     * - totalExclVat: float
     * - totalVat: float (NOT vatAmount!)
     * - totalInclVat: float
     *
     * @return array<int, array{vatType: int, vatPercentage: float, totalExclVat: float, totalVat: float, totalInclVat: float}>
     */
    private function buildVatTotals(): array
    {
        $totals = [];

        foreach ($this->lines as $line) {
            $vatPercentage = round($line->vatPercentage, 2);
            $key = (string) $vatPercentage;

            if (! isset($totals[$key])) {
                $totals[$key] = [
                    'vatType' => $line->vatType,
                    'vatPercentage' => $vatPercentage,
                    'totalExclVat' => 0.0,
                    'totalVat' => 0.0,
                    'totalInclVat' => 0.0,
                ];
            }

            $lineTotal = $line->totalExclVat ?? ($line->quantity * $line->unitPrice);
            $lineVat = $line->vatAmount ?? ($lineTotal * ($line->vatPercentage / 100));

            $totals[$key]['totalExclVat'] += $lineTotal;
            $totals[$key]['totalVat'] += $lineVat;
            $totals[$key]['totalInclVat'] += ($lineTotal + $lineVat);
        }

        // Round the totals and return as indexed array
        return array_values(array_map(function (array $total): array {
            return [
                'vatType' => $total['vatType'],
                'vatPercentage' => round($total['vatPercentage'], 2),
                'totalExclVat' => round($total['totalExclVat'], 2),
                'totalVat' => round($total['totalVat'], 2),
                'totalInclVat' => round($total['totalInclVat'], 2),
            ];
        }, $totals));
    }
}
