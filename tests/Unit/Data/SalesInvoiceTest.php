<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\Common\Address;
use Deinte\ScradaSdk\Data\Common\Customer;
use Deinte\ScradaSdk\Data\SalesInvoice\InvoiceLine;
use Deinte\ScradaSdk\Data\SalesInvoice\SalesInvoice;
use Deinte\ScradaSdk\Enums\VatType;

it('converts invoice to array payload', function (): void {
    $invoice = new SalesInvoice(
        id: 'uuid',
        bookYear: '2025',
        journal: 'SALES',
        number: '2025-001',
        creditInvoice: false,
        invoiceDate: '2025-06-03',
        invoiceExpiryDate: '2025-06-30',
        totalInclVat: 121.0,
        totalExclVat: 100.0,
        totalVat: 21.0,
        customer: new Customer(
            code: 'CUST01',
            name: 'Customer',
            email: 'customer@example.com',
            vatNumber: 'BE0123456789',
            address: new Address('Street', '1', 'Brussels', '1000', 'BE'),
        ),
        lines: [
            new InvoiceLine('Service', 1, 100.0, 21.0, VatType::STANDARD),
        ],
        alreadySentToCustomer: true,
        status: 'queued',
    );

    $payload = $invoice->toArray();

    expect($payload['bookYear'])->toBe('2025');
    expect($payload['lines'])->toBeArray();
    expect(is_array($payload['lines']))->toBeTrue();

    $lines = $payload['lines'];
    if (is_array($lines) && isset($lines[0]) && is_array($lines[0])) {
        expect($lines[0]['itemName'])->toBe('Service');
    }

    expect($payload['customer'])->toBeArray();
    $customer = $payload['customer'];
    if (is_array($customer)) {
        expect($customer['code'])->toBe('CUST01');
    }

    expect($payload['status'])->toBe('queued');
});

it('hydrates invoice from response array', function (): void {
    $invoice = SalesInvoice::fromArray([
        'id' => 'uuid',
        'bookYear' => '2025',
        'journal' => 'SALES',
        'number' => '2025-001',
        'creditInvoice' => false,
        'invoiceDate' => '2025-06-03',
        'invoiceExpiryDate' => '2025-06-30',
        'totalInclVat' => 121,
        'totalExclVat' => 100,
        'totalVat' => 21,
        'customer' => [
            'code' => 'CUST01',
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'vatNumber' => 'BE0123456789',
            'address' => [
                'street' => 'Street',
                'streetNumber' => '1',
                'city' => 'Brussels',
                'zipCode' => '1000',
                'countryCode' => 'BE',
            ],
        ],
        'lines' => [
            [
                'description' => 'Service',
                'quantity' => 1,
                'unitPrice' => 100,
                'vatPerc' => 21,
            ],
        ],
        'status' => 'queued',
    ]);

    expect($invoice->customer->code)->toBe('CUST01')
        ->and($invoice->lines[0]->description)->toBe('Service')
        ->and($invoice->status)->toBe('queued');
});
