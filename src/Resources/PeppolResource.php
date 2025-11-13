<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources;

use Deinte\ScradaSdk\Dto\Customer;
use Deinte\ScradaSdk\Dto\PeppolLookupResult;
use Deinte\ScradaSdk\Requests\Peppol\LookupPartyRequest;
use Deinte\ScradaSdk\Resources\Concerns\HandlesResponseErrors;
use Saloon\Http\BaseResource;

/**
 * Peppol specific endpoints.
 */
final class PeppolResource extends BaseResource
{
    use HandlesResponseErrors;

    /**
     * Perform a Peppol lookup for a customer.
     *
     * @param array<string, mixed>|Customer $payload
     */
    public function lookupParty(array|Customer $payload): PeppolLookupResult
    {
        $body = $payload instanceof Customer ? $payload->toArray() : $payload;
        $response = $this->connector->send(new LookupPartyRequest($body));

        $this->throwIfError($response);

        $data = $response->json();

        if (!is_array($data)) {
            return new PeppolLookupResult(false, false, false, false, false);
        }

        return PeppolLookupResult::fromArray($data);
    }
}
