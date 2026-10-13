<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\Common\Address;
use Deinte\ScradaSdk\Data\Common\Customer;
use Deinte\ScradaSdk\Exceptions\InvalidJsonResponseException;
use Deinte\ScradaSdk\Exceptions\ScradaException;
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

it('throws InvalidJsonResponseException when API returns HTML', function (): void {
    $htmlError = '<!DOCTYPE html><html><body><h1>503 Service Unavailable</h1></body></html>';

    $mockClient = new MockClient([
        LookupPartyRequest::class => MockResponse::make(
            body: $htmlError,
            status: 200,
            headers: ['Content-Type' => 'text/html'],
        ),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $customer = new Customer(
        code: 'CUST01',
        name: 'Test',
        email: '',
        vatNumber: 'BE0123456789',
        address: new Address(
            street: 'Main',
            streetNumber: '1',
            city: 'Brussels',
            zipCode: '1000',
            countryCode: 'BE',
        ),
    );

    $resource = new PeppolResource($connector);

    expect(fn () => $resource->lookupParty($customer))
        ->toThrow(InvalidJsonResponseException::class);
});

it('provides helpful error message when API returns invalid JSON', function (): void {
    $invalidJson = 'This is not valid JSON at all';

    $mockClient = new MockClient([
        LookupPartyRequest::class => MockResponse::make(
            body: $invalidJson,
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $customer = new Customer(
        code: 'CUST01',
        name: 'Test',
        email: '',
        vatNumber: 'BE0123456789',
        address: new Address(
            street: 'Main',
            streetNumber: '1',
            city: 'Brussels',
            zipCode: '1000',
            countryCode: 'BE',
        ),
    );

    $resource = new PeppolResource($connector);

    try {
        $resource->lookupParty($customer);
        test()->fail('Expected InvalidJsonResponseException');
    } catch (InvalidJsonResponseException $e) {
        expect($e->getHttpStatus())->toBe(200)
            ->and($e->getRawBody())->toBe($invalidJson)
            ->and($e->getContentType())->toBe('application/json')
            ->and($e->getMessage())->toContain('Invalid JSON response')
            ->and($e->getMessage())->toContain('HTTP 200')
            ->and($e->getMessage())->toContain('Parse error')
            ->and($e->getMessage())->toContain($invalidJson);
    }
});

it('handles empty response gracefully', function (): void {
    $mockClient = new MockClient([
        LookupPartyRequest::class => MockResponse::make(
            body: '',
            status: 200,
        ),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $customer = new Customer(
        code: 'CUST01',
        name: 'Test',
        email: '',
        vatNumber: 'BE0123456789',
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

    expect($result->canReceiveInvoices())->toBeFalse()
        ->and($result->registered)->toBeFalse();
});

it('includes raw body in ScradaException for 500 errors', function (): void {
    $htmlError = '<html><body>Internal Server Error</body></html>';

    $mockClient = new MockClient([
        LookupPartyRequest::class => MockResponse::make(
            body: $htmlError,
            status: 500,
            headers: ['Content-Type' => 'text/html'],
        ),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $customer = new Customer(
        code: 'CUST01',
        name: 'Test',
        email: '',
        vatNumber: 'BE0123456789',
        address: new Address(
            street: 'Main',
            streetNumber: '1',
            city: 'Brussels',
            zipCode: '1000',
            countryCode: 'BE',
        ),
    );

    $resource = new PeppolResource($connector);

    try {
        $resource->lookupParty($customer);
        test()->fail('Expected ScradaException');
    } catch (ScradaException $e) {
        expect($e->getMessage())->toContain('HTTP 500')
            ->and($e->getResponseBody())->toContain('Internal Server Error');
    }
});
