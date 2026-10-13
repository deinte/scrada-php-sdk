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

it('sends a bank transfer as Scrada wire transfer type 2', function (): void {
    $method = InvoicePaymentMethod::fromType(
        type: PaymentType::BankTransfer,
        totalToPay: 50.0,
        iban: 'BE59363083990926',
    );

    expect($method->toArray()['paymentType'])->toBe(2);
});

it('defaults to a bank transfer when the payment type is missing or unknown to the sdk', function (mixed $paymentType): void {
    $method = InvoicePaymentMethod::fromArray(['paymentType' => $paymentType]);

    expect($method->paymentType)->toBe(PaymentType::BankTransfer);
})->with([
    'missing' => [null],
    'not a scrada code' => [30],
    'not numeric' => ['wire'],
]);

it('keeps an explicit unknown payment type', function (): void {
    $method = InvoicePaymentMethod::fromArray(['paymentType' => '1']);

    expect($method->paymentType)->toBe(PaymentType::Unknown);
});
