<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\InboundDocuments;

use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Retrieve unconfirmed inbound documents.
 */
final class GetUnconfirmedDocumentsRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        /** @var ScradaConnector $connector */
        $connector = $this->connector;
        $companyId = $connector->getCompanyId();

        return sprintf('/v1/company/%s/peppol/inbound/document/unconfirmed', $companyId);
    }
}
