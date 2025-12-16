<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\Address;

it('creates address from array', function (): void {
    $address = Address::fromArray([
        'street' => 'Main Street',
        'streetNumber' => '123',
        'city' => 'Brussels',
        'zipCode' => '1000',
        'countryCode' => 'BE',
    ]);

    expect($address->street)->toBe('Main Street')
        ->and($address->streetNumber)->toBe('123')
        ->and($address->city)->toBe('Brussels')
        ->and($address->zipCode)->toBe('1000')
        ->and($address->countryCode)->toBe('BE');
});

it('handles missing address fields', function (): void {
    $address = Address::fromArray([]);

    expect($address->street)->toBe('')
        ->and($address->city)->toBe('')
        ->and($address->toArray())->toBe([
            'street' => '',
            'streetNumber' => '',
            'city' => '',
            'zipCode' => '',
            'countryCode' => '',
        ]);
});
