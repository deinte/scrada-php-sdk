<?php

declare(strict_types=1);

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

    $resource = new PeppolResource($connector);
    $result = $resource->lookupParty([
        'code' => 'CUST01',
        'name' => 'Customer',
        'address' => [
            'street' => 'Main',
            'streetNumber' => '1',
            'city' => 'Brussels',
            'zipCode' => '1000',
            'countryCode' => 'BE',
        ],
    ]);

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

    $resource = new PeppolResource($connector);
    $result = $resource->lookupParty([
        'name' => 'Unknown Company',
        'address' => [
            'countryCode' => 'BE',
        ],
    ]);

    expect($result->registered)->toBeFalse()
        ->and($result->canReceiveInvoices())->toBeFalse();
});
