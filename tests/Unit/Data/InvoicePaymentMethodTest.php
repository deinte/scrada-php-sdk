<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\SalesInvoice\InvoicePaymentMethod;
use Deinte\ScradaSdk\Enums\PaymentType;

it('builds a payment method with iban and bic from an array', function (): void {
    $method = InvoicePaymentMethod::fromArray([
        'paymentType' => PaymentType::BankTransfer->value,
        'name' => 'Overschrijving',
        'totalToPay' => 200.0,
        'paymentReference' => '123456789012',
        'iban' => 'BE59363083990926',
        'bic' => 'BBRUBEBB',
    ]);

    expect($method->iban)->toBe('BE59363083990926')
        ->and($method->bic)->toBe('BBRUBEBB');

    expect($method->toArray())->toBe([
        'paymentType' => PaymentType::BankTransfer->value,
        'name' => 'Overschrijving',
        'totalToPay' => 200.0,
        'paymentReference' => '123456789012',
        'iban' => 'BE59363083990926',
        'bic' => 'BBRUBEBB',
    ]);
});

it('omits iban and bic from the payload when not provided', function (): void {
    $method = InvoicePaymentMethod::fromType(
        type: PaymentType::BankTransfer,
        totalToPay: 50.0,
    );

    expect($method->iban)->toBeNull()
        ->and($method->bic)->toBeNull()
        ->and($method->toArray())->not->toHaveKeys(['iban', 'bic']);
});
