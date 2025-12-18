<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\Common\Address;
use Deinte\ScradaSdk\Data\Common\Customer;
use Deinte\ScradaSdk\Data\SalesInvoice\CreateSalesInvoiceData;
use Deinte\ScradaSdk\Data\SalesInvoice\CreateSalesInvoiceResponse;
use Deinte\ScradaSdk\Data\SalesInvoice\InvoiceLine;
use Deinte\ScradaSdk\Data\SalesInvoice\SendStatusResponse;
use Deinte\ScradaSdk\Enums\SendMethod;
use Deinte\ScradaSdk\Enums\SendStatus;
use Deinte\ScradaSdk\Requests\SalesInvoices\CreateSalesInvoiceRequest;
use Deinte\ScradaSdk\Requests\SalesInvoices\GetSalesInvoiceSendStatusRequest;
use Deinte\ScradaSdk\Resources\SalesInvoiceResource;
use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('creates a sales invoice', function (): void {
    $customer = new Customer(
        code: 'CUST01',
        name: 'Customer',
        email: 'customer@example.com',
        vatNumber: 'BE0123456789',
        address: new Address(
            street: 'Main',
            streetNumber: '1',
            city: 'Brussels',
            zipCode: '1000',
            countryCode: 'BE',
        ),
    );

    $line = new InvoiceLine(
        description: 'Service',
        quantity: 1,
        unitPrice: 100,
        vatPercentage: 21,
    );

    $data = new CreateSalesInvoiceData(
        bookYear: '2025',
        journal: 'SALES',
        number: '2025-001',
        creditInvoice: false,
        invoiceDate: '2025-06-03',
        invoiceExpiryDate: '2025-06-30',
        totalInclVat: 121,
        totalExclVat: 100,
        totalVat: 21,
        customer: $customer,
        lines: [$line],
    );

    $mockClient = new MockClient([
        CreateSalesInvoiceRequest::class => MockResponse::make([
            'id' => 'invoice-id',
            'status' => 'queued',
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $resource = new SalesInvoiceResource($connector);
    $response = $resource->create($data);

    expect($response)->toBeInstanceOf(CreateSalesInvoiceResponse::class)
        ->and($response->status)->toBe('queued')
        ->and($response->id)->toBe('invoice-id');
});

it('fetches the send status of a sales invoice', function (): void {
    $mockClient = new MockClient([
        GetSalesInvoiceSendStatusRequest::class => MockResponse::make([
            'id' => 'doc-123',
            'status' => 'Processed',
            'sendMethod' => 'Peppol',
            'createdOn' => '2025-06-03T10:00:00Z',
            'peppolSenderID' => '0208:BE0123456789',
            'peppolReceiverID' => '0208:BE9876543210',
            'attempt' => 1,
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $resource = new SalesInvoiceResource($connector);
    $response = $resource->getSendStatus('invoice-id');

    expect($response)->toBeInstanceOf(SendStatusResponse::class)
        ->and($response->status)->toBe(SendStatus::PROCESSED)
        ->and($response->sendMethod)->toBe(SendMethod::PEPPOL)
        ->and($response->isSuccess())->toBeTrue()
        ->and($response->wasSentViaPeppol())->toBeTrue()
        ->and($response->peppolSenderID)->toBe('0208:BE0123456789');
});

it('handles pending send status', function (): void {
    $mockClient = new MockClient([
        GetSalesInvoiceSendStatusRequest::class => MockResponse::make([
            'status' => 'Created',
            'sendMethod' => 'Email',
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $resource = new SalesInvoiceResource($connector);
    $response = $resource->getSendStatus('invoice-id');

    expect($response->status)->toBe(SendStatus::CREATED)
        ->and($response->isPending())->toBeTrue()
        ->and($response->isSuccess())->toBeFalse()
        ->and($response->wasSentViaEmail())->toBeTrue();
});

it('handles error send status', function (): void {
    $mockClient = new MockClient([
        GetSalesInvoiceSendStatusRequest::class => MockResponse::make([
            'status' => 'Error',
            'errorMessage' => 'Receiver not found on Peppol network',
            'attempt' => 3,
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $resource = new SalesInvoiceResource($connector);
    $response = $resource->getSendStatus('invoice-id');

    expect($response->status)->toBe(SendStatus::ERROR)
        ->and($response->isError())->toBeTrue()
        ->and($response->errorMessage)->toBe('Receiver not found on Peppol network')
        ->and($response->attempt)->toBe(3);
});
