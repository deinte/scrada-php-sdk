<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\InboundDocuments;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Download an inbound document as PDF.
 */
final class GetDocumentPdfRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $companyId,
        private readonly string $documentId,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return sprintf('/v1/company/%s/peppol/inbound/document/%s/pdf', $this->companyId, $this->documentId);
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/pdf',
        ];
    }
}
