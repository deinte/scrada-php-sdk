<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\CreateSalesInvoiceResponse;

it('creates response from array', function (): void {
    $data = [
        'id' => 'invoice-uuid',
        'status' => 'queued',
        'extraField' => 'value',
    ];

    $response = CreateSalesInvoiceResponse::fromArray($data);

    expect($response->id)->toBe('invoice-uuid')
        ->and($response->status)->toBe('queued')
        ->and($response->raw)->toBe($data);
});

it('preserves raw response data', function (): void {
    $data = [
        'id' => 'invoice-uuid',
        'status' => 'queued',
        'futureField' => 'future-value',
        'anotherField' => 123,
    ];

    $response = CreateSalesInvoiceResponse::fromArray($data);

    expect($response->raw)->toHaveKey('futureField')
        ->and($response->raw['futureField'])->toBe('future-value')
        ->and($response->raw['anotherField'])->toBe(123);
});

it('converts back to array', function (): void {
    $data = [
        'id' => 'invoice-uuid',
        'status' => 'queued',
    ];

    $response = CreateSalesInvoiceResponse::fromArray($data);
    $array = $response->toArray();

    expect($array)->toBe($data);
});

it('handles missing fields gracefully', function (): void {
    $response = CreateSalesInvoiceResponse::fromArray([]);

    expect($response->id)->toBe('')
        ->and($response->status)->toBe('draft');
});
