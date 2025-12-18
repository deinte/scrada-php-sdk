<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\Common\Address;
use Deinte\ScradaSdk\Data\Common\Customer;
use Deinte\ScradaSdk\Requests\Peppol\LookupPartyRequest;
use Deinte\ScradaSdk\Resources\PeppolResource;
use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('performs a peppol lookup', function (): void {
    $mockClient = new MockClient([
        LookupPartyRequest::class => MockResponse::make([
            'registered' => true,
            'supportInvoice' => true,
            'supportCreditInvoice' => false,
            'supportSelfBillingInvoice' => false,
            'supportSelfBillingCreditInvoice' => false,
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $customer = new Customer(
        code: 'CUST01',
        name: 'Customer',
        email: '',
        vatNumber: '',
        address: new Address(
            street: 'Main',
            streetNumber: '1',
            city: 'Brussels',
            zipCode: '1000',
            countryCode: 'BE',
        ),
    );

    $resource = new PeppolResource($connector);
    $result = $resource->lookupParty($customer);

    expect($result->registered)->toBeTrue()
        ->and($result->supportInvoice)->toBeTrue()
        ->and($result->canReceiveInvoices())->toBeTrue();
});

it('returns false for unregistered party', function (): void {
    $mockClient = new MockClient([
        LookupPartyRequest::class => MockResponse::make([
            'registered' => false,
            'supportInvoice' => false,
            'supportCreditInvoice' => false,
            'supportSelfBillingInvoice' => false,
            'supportSelfBillingCreditInvoice' => false,
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $customer = new Customer(
        code: '',
        name: 'Unknown Company',
        email: '',
        vatNumber: '',
        address: new Address(
            street: '',
            streetNumber: '',
            city: '',
            zipCode: '',
            countryCode: 'BE',
        ),
    );

    $resource = new PeppolResource($connector);
    $result = $resource->lookupParty($customer);

    expect($result->registered)->toBeFalse()
        ->and($result->canReceiveInvoices())->toBeFalse();
});
