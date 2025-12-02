<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources;

use Deinte\ScradaSdk\Dto\Customer;
use Deinte\ScradaSdk\Dto\PeppolLookupResult;
use Deinte\ScradaSdk\Exceptions\AuthenticationException;
use Deinte\ScradaSdk\Exceptions\ScradaException;
use Deinte\ScradaSdk\Exceptions\ValidationException;
use Deinte\ScradaSdk\Requests\Peppol\LookupPartyRequest;
use Deinte\ScradaSdk\Resources\Concerns\HandlesResponseErrors;
use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Http\BaseResource;

/**
 * Peppol specific endpoints.
 *
 * @property ScradaConnector $connector
 */
final class PeppolResource extends BaseResource
{
    use HandlesResponseErrors;

    /**
     * Perform a Peppol lookup for a customer.
     *
     * @param  array<string, mixed>|Customer  $payload
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function lookupParty(array|Customer $payload): PeppolLookupResult
    {
        $body = $payload instanceof Customer ? $payload->toArray() : $payload;

        ray()->send($this->connector->getCompanyId(), $body);

        $response = $this->connector->send(new LookupPartyRequest(
            $this->connector->getCompanyId(),
            $body
        ));

        $this->throwIfError($response);

        ray()->send($response->status(), $response->ok(), $response->json());

        $data = $response->json();

        if (! is_array($data)) {
            return new PeppolLookupResult(false, false, false, false, false);
        }

        return PeppolLookupResult::fromArray($data);
    }
}
