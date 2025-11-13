<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Requests\DailyReceipts\GetPaymentMethodsRequest;
use Deinte\ScradaSdk\Resources\DailyReceiptsResource;
use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('fetches payment methods via Saloon mock', function (): void {
    $mockClient = new MockClient([
        GetPaymentMethodsRequest::class => MockResponse::make([
            ['id' => 'pm-1', 'name' => 'Cash', 'type' => 'cash'],
        ]),
    ]);

    $connector = new ScradaConnector('key', 'secret', 'company');
    $connector->withMockClient($mockClient);

    $resource = new DailyReceiptsResource($connector);
    $methods = $resource->getPaymentMethods('journal');

    expect($methods)->toHaveCount(1)
        ->and($methods[0]->name)->toBe('Cash');
});
