<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\InboundDocuments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Retrieve unconfirmed inbound documents.
 */
final class GetUnconfirmedDocumentsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $companyId,
    ) {}

    public function resolveEndpoint(): string
    {
        return sprintf('/v1/company/%s/peppol/inbound/document/unconfirmed', $this->companyId);
    }
}
