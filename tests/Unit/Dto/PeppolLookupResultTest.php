<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Dto\PeppolLookupResult;

it('creates result from array with Scrada API field names', function (): void {
    $data = [
        'registered' => true,
        'supportInvoice' => true,
        'supportCreditInvoice' => false,
        'supportSelfBillingInvoice' => true,
        'supportSelfBillingCreditInvoice' => false,
    ];

    $result = PeppolLookupResult::fromArray($data);

    expect($result->registered)->toBeTrue()
        ->and($result->supportInvoice)->toBeTrue()
        ->and($result->supportCreditInvoice)->toBeFalse()
        ->and($result->supportSelfBillingInvoice)->toBeTrue()
        ->and($result->supportSelfBillingCreditInvoice)->toBeFalse()
        ->and($result->meta)->toBe($data);
});

it('can check if party can receive invoices', function (): void {
    // Registered AND supports invoice
    $result = PeppolLookupResult::fromArray([
        'registered' => true,
        'supportInvoice' => true,
    ]);
    expect($result->canReceiveInvoices())->toBeTrue();

    // Registered but does NOT support invoice
    $result = PeppolLookupResult::fromArray([
        'registered' => true,
        'supportInvoice' => false,
    ]);
    expect($result->canReceiveInvoices())->toBeFalse();

    // NOT registered but supports invoice
    $result = PeppolLookupResult::fromArray([
        'registered' => false,
        'supportInvoice' => true,
    ]);
    expect($result->canReceiveInvoices())->toBeFalse();
});

it('can check if party can receive credit invoices', function (): void {
    $result = PeppolLookupResult::fromArray([
        'registered' => true,
        'supportCreditInvoice' => true,
    ]);
    expect($result->canReceiveCreditInvoices())->toBeTrue();

    $result = PeppolLookupResult::fromArray([
        'registered' => false,
        'supportCreditInvoice' => true,
    ]);
    expect($result->canReceiveCreditInvoices())->toBeFalse();
});

it('converts to array with Scrada API field names', function (): void {
    $result = new PeppolLookupResult(
        registered: true,
        supportInvoice: true,
        supportCreditInvoice: false,
        supportSelfBillingInvoice: true,
        supportSelfBillingCreditInvoice: false,
    );

    $array = $result->toArray();

    expect($array)->toBe([
        'registered' => true,
        'supportInvoice' => true,
        'supportCreditInvoice' => false,
        'supportSelfBillingInvoice' => true,
        'supportSelfBillingCreditInvoice' => false,
    ]);
});

it('handles missing fields gracefully', function (): void {
    $result = PeppolLookupResult::fromArray([]);

    expect($result->registered)->toBeFalse()
        ->and($result->supportInvoice)->toBeFalse()
        ->and($result->supportCreditInvoice)->toBeFalse()
        ->and($result->supportSelfBillingInvoice)->toBeFalse()
        ->and($result->supportSelfBillingCreditInvoice)->toBeFalse()
        ->and($result->canReceiveInvoices())->toBeFalse();
});

it('preserves meta data from original response', function (): void {
    $data = [
        'registered' => true,
        'supportInvoice' => true,
        'supportCreditInvoice' => false,
        'supportSelfBillingInvoice' => false,
        'supportSelfBillingCreditInvoice' => false,
        'extraField' => 'extra-value',
        'anotherField' => 123,
    ];

    $result = PeppolLookupResult::fromArray($data);

    expect($result->meta)->toHaveKey('extraField')
        ->and($result->meta['extraField'])->toBe('extra-value')
        ->and($result->meta['anotherField'])->toBe(123);
});
