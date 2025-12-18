<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\InboundDocuments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Retrieve a single inbound document.
 */
final class GetDocumentRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $companyId,
        private readonly string $documentId,
    ) {}

    public function resolveEndpoint(): string
    {
        return sprintf('/v1/company/%s/peppol/inbound/document/%s', $this->companyId, $this->documentId);
    }
}
