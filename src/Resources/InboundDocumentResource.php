<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources;

use Closure;
use Deinte\ScradaSdk\Dto\InboundDocument;
use Deinte\ScradaSdk\Exceptions\NotFoundException;
use Deinte\ScradaSdk\Requests\InboundDocuments\ConfirmDocumentRequest;
use Deinte\ScradaSdk\Requests\InboundDocuments\GetDocumentPdfRequest;
use Deinte\ScradaSdk\Requests\InboundDocuments\GetDocumentRequest;
use Deinte\ScradaSdk\Requests\InboundDocuments\GetUnconfirmedDocumentsRequest;
use Deinte\ScradaSdk\Resources\Concerns\HandlesResponseErrors;
use Saloon\Http\BaseResource;

/**
 * Inbound document operations.
 */
final class InboundDocumentResource extends BaseResource
{
    use HandlesResponseErrors;

    /**
     * Fetch all unconfirmed inbound documents.
     *
     * @return array<int, InboundDocument>
     */
    public function getUnconfirmed(): array
    {
        $response = $this->connector->send(new GetUnconfirmedDocumentsRequest());

        $this->throwIfError($response);

        $data = $response->json();

        if (!is_array($data)) {
            return [];
        }

        $items = array_filter(
            $data,
            static fn (mixed $item): bool => is_array($item)
        );

        return array_map(
            static fn (array $item): InboundDocument => InboundDocument::fromArray($item),
            array_values($items)
        );
    }

    /**
     * Fetch a single inbound document by ID.
     */
    public function get(string $documentId): InboundDocument
    {
        $response = $this->connector->send(new GetDocumentRequest($documentId));

        $this->throwIfError($response, $this->notFoundFactory($documentId));

        $data = $response->json();

        if (!is_array($data)) {
            return new InboundDocument($documentId, '', '', '', '', 0.0, null, null);
        }

        return InboundDocument::fromArray($data);
    }

    /**
     * Download the PDF for an inbound document.
     */
    public function getPdf(string $documentId): string
    {
        $response = $this->connector->send(new GetDocumentPdfRequest($documentId));

        $this->throwIfError($response, $this->notFoundFactory($documentId));

        return $response->body();
    }

    /**
     * Confirm that a document has been handled.
     */
    public function confirm(string $documentId): bool
    {
        $response = $this->connector->send(new ConfirmDocumentRequest($documentId));

        $this->throwIfError($response, $this->notFoundFactory($documentId));

        $data = $response->json();

        if (!is_array($data)) {
            return true;
        }

        if (array_key_exists('confirmed', $data)) {
            return (bool) $data['confirmed'];
        }

        if (array_key_exists('status', $data)) {
            return $data['status'] === 'confirmed';
        }

        return true;
    }

    /**
     * @return Closure():NotFoundException
     */
    private function notFoundFactory(string $documentId): Closure
    {
        return static fn (): NotFoundException => NotFoundException::resource('Inbound document', $documentId);
    }
}
