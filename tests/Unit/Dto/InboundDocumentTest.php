<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Dto\InboundDocument;

it('creates document from array', function (): void {
    $data = [
        'id' => 'doc-uuid',
        'documentNumber' => 'INV-2025-001',
        'supplierName' => 'Supplier Co',
        'status' => 'received',
        'receivedAt' => '2025-01-15T10:30:00Z',
        'totalInclVat' => 121.0,
        'totalExclVat' => 100.0,
        'currency' => 'EUR',
    ];

    $document = InboundDocument::fromArray($data);

    expect($document->id)->toBe('doc-uuid')
        ->and($document->documentNumber)->toBe('INV-2025-001')
        ->and($document->supplierName)->toBe('Supplier Co')
        ->and($document->status)->toBe('received')
        ->and($document->receivedAt)->toBe('2025-01-15T10:30:00Z')
        ->and($document->totalInclVat)->toBe(121.0)
        ->and($document->totalExclVat)->toBe(100.0)
        ->and($document->currency)->toBe('EUR')
        ->and($document->raw)->toBe($data);
});

it('handles alternative field names', function (): void {
    $data = [
        'id' => 'doc-uuid',
        'number' => 'INV-2025-001',
        'supplierName' => 'Supplier Co',
        'status' => 'received',
        'createdAt' => '2025-01-15T10:30:00Z',
        'totalInclVat' => 121.0,
    ];

    $document = InboundDocument::fromArray($data);

    expect($document->documentNumber)->toBe('INV-2025-001')
        ->and($document->receivedAt)->toBe('2025-01-15T10:30:00Z');
});

it('converts to array', function (): void {
    $document = new InboundDocument(
        id: 'doc-uuid',
        documentNumber: 'INV-2025-001',
        supplierName: 'Supplier Co',
        status: 'received',
        receivedAt: '2025-01-15T10:30:00Z',
        totalInclVat: 121.0,
        totalExclVat: 100.0,
        currency: 'EUR'
    );

    $array = $document->toArray();

    expect($array)->toBe([
        'id' => 'doc-uuid',
        'documentNumber' => 'INV-2025-001',
        'supplierName' => 'Supplier Co',
        'status' => 'received',
        'receivedAt' => '2025-01-15T10:30:00Z',
        'totalInclVat' => 121.0,
        'totalExclVat' => 100.0,
        'currency' => 'EUR',
    ]);
});

it('excludes null fields from toArray output', function (): void {
    $document = new InboundDocument(
        id: 'doc-uuid',
        documentNumber: 'INV-2025-001',
        supplierName: 'Supplier Co',
        status: 'received',
        receivedAt: '2025-01-15T10:30:00Z',
        totalInclVat: 121.0,
        totalExclVat: null,
        currency: null
    );

    $array = $document->toArray();

    expect($array)->toBe([
        'id' => 'doc-uuid',
        'documentNumber' => 'INV-2025-001',
        'supplierName' => 'Supplier Co',
        'status' => 'received',
        'receivedAt' => '2025-01-15T10:30:00Z',
        'totalInclVat' => 121.0,
    ])
        ->and($array)->not->toHaveKey('totalExclVat')
        ->and($array)->not->toHaveKey('currency');
});

it('handles missing fields gracefully', function (): void {
    $document = InboundDocument::fromArray([]);

    expect($document->id)->toBe('')
        ->and($document->documentNumber)->toBe('')
        ->and($document->supplierName)->toBe('')
        ->and($document->status)->toBe('')
        ->and($document->receivedAt)->toBe('')
        ->and($document->totalInclVat)->toBe(0.0)
        ->and($document->totalExclVat)->toBeNull()
        ->and($document->currency)->toBeNull();
});

it('preserves raw data from original response', function (): void {
    $data = [
        'id' => 'doc-uuid',
        'documentNumber' => 'INV-2025-001',
        'supplierName' => 'Supplier Co',
        'status' => 'received',
        'receivedAt' => '2025-01-15T10:30:00Z',
        'totalInclVat' => 121.0,
        'extraField' => 'extra-value',
        'futureField' => ['nested' => 'data'],
    ];

    $document = InboundDocument::fromArray($data);

    expect($document->raw)->toHaveKey('extraField')
        ->and($document->raw['extraField'])->toBe('extra-value')
        ->and($document->raw['futureField'])->toBe(['nested' => 'data']);
});

it('handles integer amounts by converting to float', function (): void {
    $data = [
        'id' => 'doc-uuid',
        'documentNumber' => 'INV-2025-001',
        'supplierName' => 'Supplier Co',
        'status' => 'received',
        'receivedAt' => '2025-01-15T10:30:00Z',
        'totalInclVat' => 121,
        'totalExclVat' => 100,
    ];

    $document = InboundDocument::fromArray($data);

    expect($document->totalInclVat)->toBe(121.0)
        ->and($document->totalExclVat)->toBe(100.0);
});
