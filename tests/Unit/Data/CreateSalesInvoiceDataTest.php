<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\Common\Address;
use Deinte\ScradaSdk\Data\Common\Attachment;
use Deinte\ScradaSdk\Data\Common\Customer;
use Deinte\ScradaSdk\Data\SalesInvoice\CreateSalesInvoiceData;
use Deinte\ScradaSdk\Data\SalesInvoice\InvoiceLine;

it('creates request data from array', function (): void {
    $data = [
        'bookYear' => '2025',
        'journal' => 'SALES',
        'number' => '2025-001',
        'creditInvoice' => false,
        'invoiceDate' => '2025-06-03',
        'invoiceExpiryDate' => '2025-06-30',
        'totalInclVat' => 121.0,
        'totalExclVat' => 100.0,
        'totalVat' => 21.0,
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
                'unitPrice' => 100.0,
                'vatPerc' => 21.0,
            ],
        ],
        'alreadySentToCustomer' => true,
    ];

    $requestData = CreateSalesInvoiceData::fromArray($data);

    expect($requestData->bookYear)->toBe('2025')
        ->and($requestData->journal)->toBe('SALES')
        ->and($requestData->number)->toBe('2025-001')
        ->and($requestData->alreadySentToCustomer)->toBeTrue()
        ->and($requestData->lines)->toHaveCount(1)
        ->and($requestData->customer)->toBeInstanceOf(Customer::class);
});

it('converts request data to array payload', function (): void {
    $requestData = new CreateSalesInvoiceData(
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
            new InvoiceLine('Service', 1, 100.0, 21.0),
        ],
        alreadySentToCustomer: true,
    );

    $payload = $requestData->toArray();

    expect($payload)->toHaveKey('bookYear')
        ->and($payload['bookYear'])->toBe('2025')
        ->and($payload)->toHaveKey('alreadySendToCustomer')
        ->and($payload['alreadySendToCustomer'])->toBeTrue()
        ->and($payload['lines'])->toHaveCount(1)
        ->and($payload['customer'])->toBeArray();
});

it('sends the buyer reference next to the order references', function (): void {
    $requestData = CreateSalesInvoiceData::fromArray([
        'bookYear' => '2025',
        'journal' => 'SALES',
        'number' => '2025-001',
        'invoiceDate' => '2025-06-03',
        'invoiceExpiryDate' => '2025-06-30',
        'customer' => ['code' => 'CUST01', 'name' => 'Customer'],
        'lines' => [
            ['description' => 'Service', 'quantity' => 1, 'unitPrice' => 100.0, 'vatPerc' => 21.0],
        ],
        'buyerReference' => 'PO-4512',
        'purchaseOrderReference' => 'PO-4512',
    ]);

    $payload = $requestData->toArray();

    expect($requestData->buyerReference)->toBe('PO-4512')
        ->and($payload['buyerReference'])->toBe('PO-4512')
        ->and($payload['purchaseOrderReference'])->toBe('PO-4512');
});

it('keeps the buyer reference when an attachment is added', function (): void {
    $requestData = CreateSalesInvoiceData::fromArray([
        'customer' => ['code' => 'CUST01', 'name' => 'Customer'],
        'buyerReference' => 'PO-4512',
    ])->withAttachment(Attachment::pdf('invoice.pdf', 'JVBERi0='));

    expect($requestData->buyerReference)->toBe('PO-4512');
});

it('leaves the buyer reference out of the payload when it is missing', function (): void {
    $payload = CreateSalesInvoiceData::fromArray([
        'customer' => ['code' => 'CUST01', 'name' => 'Customer'],
    ])->toArray();

    expect($payload)->not->toHaveKey('buyerReference');
});
