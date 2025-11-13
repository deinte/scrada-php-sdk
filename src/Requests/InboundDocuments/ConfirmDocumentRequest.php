<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\InboundDocuments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Confirm an inbound document.
 */
final class ConfirmDocumentRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly string $companyId,
        private readonly string $documentId,
    ) {}

    public function resolveEndpoint(): string
    {
        return sprintf('/v1/company/%s/peppol/inbound/document/%s/confirm', $this->companyId, $this->documentId);
    }
}
