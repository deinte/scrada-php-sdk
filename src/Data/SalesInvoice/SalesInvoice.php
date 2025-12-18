<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\SalesInvoice;

use Deinte\ScradaSdk\Data\Common\Customer;

/**
 * Represents a Scrada sales invoice (response object).
 */
final readonly class SalesInvoice
{
    /**
     * @param  array<int, InvoiceLine>  $lines
     */
    public function __construct(
        public ?string $id,
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
        public string $status = 'draft',
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
                callback: static fn (mixed $line): bool => is_array($line),
            )),
        );

        $id = $data['id'] ?? null;
        $customer = $data['customer'] ?? null;
        $totalInclVat = $data['totalInclVat'] ?? null;
        $totalExclVat = $data['totalExclVat'] ?? null;
        $totalVat = $data['totalVat'] ?? null;
        $alreadySent = $data['alreadySendToCustomer'] ?? $data['alreadySentToCustomer'] ?? null;

        return new self(
            id: is_string($id) ? $id : null,
            bookYear: is_string($data['bookYear'] ?? null) ? $data['bookYear'] : '',
            journal: is_string($data['journal'] ?? null) ? $data['journal'] : '',
            number: is_string($data['number'] ?? null) ? $data['number'] : '',
            creditInvoice: (bool) ($data['creditInvoice'] ?? false),
            invoiceDate: is_string($data['invoiceDate'] ?? null) ? $data['invoiceDate'] : '',
            invoiceExpiryDate: is_string($data['invoiceExpiryDate'] ?? null) ? $data['invoiceExpiryDate'] : '',
            totalInclVat: is_numeric($totalInclVat) ? (float) $totalInclVat : 0.0,
            totalExclVat: is_numeric($totalExclVat) ? (float) $totalExclVat : 0.0,
            totalVat: is_numeric($totalVat) ? (float) $totalVat : 0.0,
            customer: Customer::fromArray(is_array($customer) ? $customer : []),
            lines: $lines,
            alreadySentToCustomer: (bool) $alreadySent,
            status: is_string($data['status'] ?? null) ? $data['status'] : 'draft',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'bookYear' => $this->bookYear,
            'journal' => $this->journal,
            'number' => $this->number,
            'creditInvoice' => $this->creditInvoice,
            'invoiceDate' => $this->invoiceDate,
            'invoiceExpiryDate' => $this->invoiceExpiryDate,
            'customer' => $this->customer->toArray(),
            'totalInclVat' => $this->totalInclVat,
            'totalExclVat' => $this->totalExclVat,
            'totalVat' => $this->totalVat,
            'lines' => array_map(
                static fn (InvoiceLine $line): array => $line->toArray(),
                $this->lines,
            ),
            'alreadySendToCustomer' => $this->alreadySentToCustomer,
        ];

        if ($this->id !== null) {
            $payload['id'] = $this->id;
        }

        if ($this->status !== '') {
            $payload['status'] = $this->status;
        }

        return $payload;
    }
}
