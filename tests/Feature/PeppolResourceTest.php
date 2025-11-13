<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Requests\Peppol\LookupPartyRequest;
use Deinte\ScradaSdk\Resources\PeppolResource;
use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('performs a peppol lookup', function (): void {
    MockClient::global([
        LookupPartyRequest::class => MockResponse::make([
            'invoice' => true,
            'creditNote' => false,
            'order' => true,
            'orderResponse' => false,
            'despatchAdvice' => false,
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
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

    expect($result->canReceiveInvoices)->toBeTrue()
        ->and($result->canReceiveOrders)->toBeTrue();

    MockClient::destroyGlobal();
});
