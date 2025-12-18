<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\SalesInvoice\InvoiceLine;
use Deinte\ScradaSdk\Enums\VatType;

it('creates invoice line with vatAmount', function (): void {
    $line = new InvoiceLine(
        description: 'Test Service',
        quantity: 2.0,
        unitPrice: 100.0,
        vatPercentage: 21.0,
        vatType: VatType::STANDARD,
        lineNumber: 1,
        totalExclVat: 200.0,
        vatAmount: 42.0,
    );

    expect($line->description)->toBe('Test Service')
        ->and($line->quantity)->toBe(2.0)
        ->and($line->unitPrice)->toBe(100.0)
        ->and($line->vatPercentage)->toBe(21.0)
        ->and($line->vatType)->toBe(VatType::STANDARD)
        ->and($line->lineNumber)->toBe(1)
        ->and($line->totalExclVat)->toBe(200.0)
        ->and($line->vatAmount)->toBe(42.0);
});

it('creates invoice line from array with vatAmount', function (): void {
    $data = [
        'description' => 'Test Service',
        'quantity' => 2.0,
        'unitPrice' => 100.0,
        'vatPerc' => 21.0,
        'vatType' => 1,
        'lineNumber' => 1,
        'totalExclVat' => 200.0,
        'vatAmount' => 42.0,
    ];

    $line = InvoiceLine::fromArray($data);

    expect($line->description)->toBe('Test Service')
        ->and($line->quantity)->toBe(2.0)
        ->and($line->unitPrice)->toBe(100.0)
        ->and($line->vatPercentage)->toBe(21.0)
        ->and($line->vatType)->toBe(VatType::STANDARD)
        ->and($line->lineNumber)->toBe(1)
        ->and($line->totalExclVat)->toBe(200.0)
        ->and($line->vatAmount)->toBe(42.0);
});

it('converts invoice line to array with vatAmount', function (): void {
    $line = new InvoiceLine(
        description: 'Test Service',
        quantity: 2.0,
        unitPrice: 100.0,
        vatPercentage: 21.0,
        vatType: VatType::STANDARD,
        lineNumber: 1,
        totalExclVat: 200.0,
        vatAmount: 42.0,
    );

    $payload = $line->toArray();

    expect($payload)->toHaveKey('itemName')
        ->and($payload['itemName'])->toBe('Test Service')
        ->and($payload)->toHaveKey('quantity')
        ->and($payload['quantity'])->toBe(2.0)
        ->and($payload)->toHaveKey('itemExclVat')
        ->and($payload['itemExclVat'])->toBe(100.0)
        ->and($payload)->toHaveKey('vatPercentage')
        ->and($payload['vatPercentage'])->toBe(21.0)
        ->and($payload)->toHaveKey('vatType')
        ->and($payload['vatType'])->toBe(1)
        ->and($payload)->toHaveKey('totalExclVat')
        ->and($payload['totalExclVat'])->toBe(200.0)
        ->and($payload)->toHaveKey('vatAmount')
        ->and($payload['vatAmount'])->toBe(42.0)
        ->and($payload)->toHaveKey('lineNumber')
        ->and($payload['lineNumber'])->toBe('1');
});

it('calculates vatAmount from percentage when not provided', function (): void {
    $line = new InvoiceLine(
        description: 'Test Service',
        quantity: 2.0,
        unitPrice: 100.0,
        vatPercentage: 21.0,
        vatType: VatType::STANDARD,
        lineNumber: 1,
        totalExclVat: 200.0,
        vatAmount: null,
    );

    $payload = $line->toArray();

    expect($payload['vatAmount'])->toBe(42.0);
});

it('ensures line totals match for Scrada validation', function (): void {
    $line = new InvoiceLine(
        description: 'Event Registration',
        quantity: 1.0,
        unitPrice: 150.50,
        vatPercentage: 21.0,
        vatType: VatType::STANDARD,
        lineNumber: 1,
        totalExclVat: 150.50,
        vatAmount: 31.61,
    );

    $payload = $line->toArray();

    expect($payload['vatAmount'])->toBe(31.61)
        ->and($payload['totalExclVat'])->toBe(150.50)
        ->and(round($payload['totalExclVat'] + $payload['vatAmount'], 2))->toBe(182.11);
});

it('derives vatType from vatPercentage for domestic invoices', function (): void {
    $line21 = InvoiceLine::fromArray(['description' => 'Test', 'quantity' => 1, 'unitPrice' => 100, 'vatPercentage' => 21.0]);
    $line0 = InvoiceLine::fromArray(['description' => 'Test', 'quantity' => 1, 'unitPrice' => 100, 'vatPercentage' => 0.0]);

    expect($line21->vatType)->toBe(VatType::STANDARD)
        ->and($line0->vatType)->toBe(VatType::EXEMPT);
});
