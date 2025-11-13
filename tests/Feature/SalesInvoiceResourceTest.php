<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Dto\CreateSalesInvoiceResponse;
use Deinte\ScradaSdk\Requests\SalesInvoices\CreateSalesInvoiceRequest;
use Deinte\ScradaSdk\Requests\SalesInvoices\GetSalesInvoiceSendStatusRequest;
use Deinte\ScradaSdk\Resources\SalesInvoiceResource;
use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('creates a sales invoice', function (): void {
    $payload = [
        'bookYear' => '2025',
        'journal' => 'SALES',
        'number' => '2025-001',
        'creditInvoice' => false,
        'invoiceDate' => '2025-06-03',
        'invoiceExpiryDate' => '2025-06-30',
        'customer' => [
            'code' => 'CUST01',
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'vatNumber' => 'BE0123456789',
            'address' => [
                'street' => 'Main',
                'streetNumber' => '1',
                'city' => 'Brussels',
                'zipCode' => '1000',
                'countryCode' => 'BE',
            ],
        ],
        'totalInclVat' => 121,
        'totalExclVat' => 100,
        'totalVat' => 21,
        'lines' => [
            [
                'description' => 'Service',
                'quantity' => 1,
                'unitPrice' => 100,
                'vatPerc' => 21,
                'vatTypeID' => 'vat-type',
            ],
        ],
    ];

    $mockClient = new MockClient([
        CreateSalesInvoiceRequest::class => MockResponse::make([
            'id' => 'invoice-id',
            'status' => 'queued',
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $resource = new SalesInvoiceResource($connector);
    $response = $resource->create($payload);

    expect($response)->toBeInstanceOf(CreateSalesInvoiceResponse::class)
        ->and($response->status)->toBe('queued')
        ->and($response->id)->toBe('invoice-id');
});

it('fetches the send status of a sales invoice', function (): void {
    $mockClient = new MockClient([
        GetSalesInvoiceSendStatusRequest::class => MockResponse::make([
            'status' => 'delivered',
            'peppolSent' => true,
            'emailSent' => false,
            'pending' => false,
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $resource = new SalesInvoiceResource($connector);
    $status = $resource->getSendStatus('invoice-id');

    expect($status->status)->toBe('delivered')
        ->and($status->peppolSent)->toBeTrue();
});
