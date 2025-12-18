<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\Peppol;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Perform a Peppol lookup.
 */
final class LookupPartyRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly string $companyId,
        private readonly array $payload,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return sprintf('/v1/company/%s/peppol/lookup', $this->companyId);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
