<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources;

use Deinte\ScradaSdk\Data\Common\Customer;
use Deinte\ScradaSdk\Data\PeppolLookupResult;
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
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function lookupParty(Customer $customer): PeppolLookupResult
    {
        $response = $this->connector->send(new LookupPartyRequest(
            $this->connector->getCompanyId(),
            $customer->toArray(),
        ));

        $this->throwIfError($response);

        $data = $response->json();

        if (! is_array($data)) {
            return new PeppolLookupResult(false, false, false, false, false);
        }

        return PeppolLookupResult::fromArray($data);
    }
}
