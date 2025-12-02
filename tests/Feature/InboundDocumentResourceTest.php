<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Requests\InboundDocuments\ConfirmDocumentRequest;
use Deinte\ScradaSdk\Requests\InboundDocuments\GetDocumentRequest;
use Deinte\ScradaSdk\Requests\InboundDocuments\GetUnconfirmedDocumentsRequest;
use Deinte\ScradaSdk\Resources\InboundDocumentResource;
use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('lists and confirms inbound documents', function (): void {
    $mockClient = new MockClient([
        GetUnconfirmedDocumentsRequest::class => MockResponse::make([
            [
                'id' => 'doc-1',
                'documentNumber' => 'INV-1',
                'supplierName' => 'Supplier',
                'status' => 'received',
                'receivedAt' => '2025-01-01',
                'totalInclVat' => 121,
            ],
        ]),
        GetDocumentRequest::class => MockResponse::make([
            'id' => 'doc-1',
            'documentNumber' => 'INV-1',
            'supplierName' => 'Supplier',
            'status' => 'received',
            'receivedAt' => '2025-01-01',
            'totalInclVat' => 121,
        ]),
        ConfirmDocumentRequest::class => MockResponse::make([
            'status' => 'confirmed',
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $resource = new InboundDocumentResource($connector);

    $documents = $resource->getUnconfirmed();
    $document = $resource->get('doc-1');
    $confirmed = $resource->confirm('doc-1');

    expect($documents)->toHaveCount(1)
        ->and($document->supplierName)->toBe('Supplier')
        ->and($confirmed)->toBeTrue();
});
