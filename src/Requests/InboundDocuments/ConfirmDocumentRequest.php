<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\InboundDocuments;

use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Confirm an inbound document.
 */
final class ConfirmDocumentRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly string $documentId,
    ) {}

    public function resolveEndpoint(): string
    {
        /** @var ScradaConnector $connector */
        $connector = $this->connector;
        $companyId = $connector->getCompanyId();

        return sprintf('/v1/company/%s/peppol/inbound/document/%s/confirm', $companyId, $this->documentId);
    }
}
