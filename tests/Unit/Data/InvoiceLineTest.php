<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\InvoiceLine;

it('creates invoice line with vatAmount', function (): void {
    $line = new InvoiceLine(
        description: 'Test Service',
        quantity: 2.0,
        unitPrice: 100.0,
        vatPercentage: 21.0,
        vatType: 1,
        lineNumber: 1,
        totalExclVat: 200.0,
        vatAmount: 42.0,
    );

    expect($line->description)->toBe('Test Service')
        ->and($line->quantity)->toBe(2.0)
        ->and($line->unitPrice)->toBe(100.0)
        ->and($line->vatPercentage)->toBe(21.0)
        ->and($line->vatType)->toBe(1)
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
        ->and($line->vatType)->toBe(1)
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
        vatType: 1,
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
        vatType: 1,
        lineNumber: 1,
        totalExclVat: 200.0,
        vatAmount: null, // Not provided
    );

    $payload = $line->toArray();

    // Should calculate: 200 * 0.21 = 42.0
    expect($payload['vatAmount'])->toBe(42.0);
});

it('ensures line totals match for Scrada validation', function (): void {
    // Real-world example: line with rounding
    $line = new InvoiceLine(
        description: 'Event Registration',
        quantity: 1.0,
        unitPrice: 150.50,
        vatPercentage: 21.0,
        vatType: 1,
        lineNumber: 1,
        totalExclVat: 150.50,
        vatAmount: 31.61, // Actual VAT from invoice rules
    );

    $payload = $line->toArray();

    // Verify the payload includes the exact vatAmount
    expect($payload['vatAmount'])->toBe(31.61)
        ->and($payload['totalExclVat'])->toBe(150.50)
        // Total incl VAT would be: 150.50 + 31.61 = 182.11
        ->and(round($payload['totalExclVat'] + $payload['vatAmount'], 2))->toBe(182.11);
});

it('converts vatPercentage to vatType for domestic invoices', function (): void {
    // VatType::STANDARD = 1, VatType::EXEMPT = 3
    expect(InvoiceLine::vatPercentageToTypeDomestic(21.0))->toBe(1)   // STANDARD
        ->and(InvoiceLine::vatPercentageToTypeDomestic(12.0))->toBe(1) // STANDARD (any positive %)
        ->and(InvoiceLine::vatPercentageToTypeDomestic(6.0))->toBe(1)  // STANDARD (any positive %)
        ->and(InvoiceLine::vatPercentageToTypeDomestic(0.0))->toBe(3)  // EXEMPT (domestic 0%)
        ->and(InvoiceLine::vatPercentageToTypeDomestic(99.0))->toBe(1); // STANDARD (any positive %)
});

it('converts vatPercentage to vatType for cross-border EU B2B invoices', function (): void {
    // VatType::STANDARD = 1, VatType::ICD_SERVICES_B2B = 4, VatType::ICD_GOODS = 5
    expect(InvoiceLine::vatPercentageToTypeCrossBorder(21.0))->toBe(1)            // STANDARD
        ->and(InvoiceLine::vatPercentageToTypeCrossBorder(0.0, true))->toBe(4)    // ICD_SERVICES_B2B
        ->and(InvoiceLine::vatPercentageToTypeCrossBorder(0.0, false))->toBe(5);  // ICD_GOODS
});
