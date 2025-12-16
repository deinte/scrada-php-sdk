<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources;

use Closure;
use Deinte\ScradaSdk\Data\CreateSalesInvoiceData;
use Deinte\ScradaSdk\Data\CreateSalesInvoiceResponse;
use Deinte\ScradaSdk\Data\InvoiceLine;
use Deinte\ScradaSdk\Data\SalesInvoice;
use Deinte\ScradaSdk\Data\SendStatus;
use Deinte\ScradaSdk\Exceptions\AuthenticationException;
use Deinte\ScradaSdk\Exceptions\NotFoundException;
use Deinte\ScradaSdk\Exceptions\ScradaException;
use Deinte\ScradaSdk\Exceptions\ValidationException;
use Deinte\ScradaSdk\Requests\SalesInvoices\CreateSalesInvoiceRequest;
use Deinte\ScradaSdk\Requests\SalesInvoices\GetSalesInvoiceSendStatusRequest;
use Deinte\ScradaSdk\Requests\SalesInvoices\GetSalesInvoiceUblRequest;
use Deinte\ScradaSdk\Resources\Concerns\HandlesResponseErrors;
use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Http\BaseResource;

/**
 * Sales invoice operations.
 *
 * @property ScradaConnector $connector
 */
final class SalesInvoiceResource extends BaseResource
{
    use HandlesResponseErrors;

    /**
     * Create a new sales invoice.
     *
     * @param  array<string, mixed>|CreateSalesInvoiceData|SalesInvoice  $invoice
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function create(array|CreateSalesInvoiceData|SalesInvoice $invoice): CreateSalesInvoiceResponse
    {
        $payload = match (true) {
            $invoice instanceof CreateSalesInvoiceData => $invoice->toArray(),
            $invoice instanceof SalesInvoice => $invoice->toArray(),
            default => $this->normalizeInvoicePayload($invoice),
        };

        $response = $this->connector->send(new CreateSalesInvoiceRequest(
            $this->connector->getCompanyId(),
            $payload
        ));

        $this->throwIfError($response);

        // Scrada may return a simple string (invoice ID) or a JSON object
        $body = $response->body();

        // Try to decode as JSON first
        $data = json_decode($body, true);

        // If decoding failed or result is a string, it's likely just an invoice ID
        if (! is_array($data)) {
            // The body might be a quoted string like "12345" or just 12345
            $invoiceId = is_string($data) ? $data : trim($body, '"');

            return new CreateSalesInvoiceResponse((string) $invoiceId, 'draft');
        }

        return CreateSalesInvoiceResponse::fromArray($data);
    }

    /**
     * Retrieve the send status of an invoice.
     *
     * @throws AuthenticationException
     * @throws NotFoundException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function getSendStatus(string $salesInvoiceId): SendStatus
    {
        $response = $this->connector->send(new GetSalesInvoiceSendStatusRequest(
            $this->connector->getCompanyId(),
            $salesInvoiceId
        ));

        $this->throwIfError($response, $this->notFoundFactory('Sales invoice', $salesInvoiceId));

        $data = $response->json();

        if (! is_array($data)) {
            return new SendStatus('', false, false, false);
        }

        return SendStatus::fromArray($data);
    }

    /**
     * Retrieve the UBL XML for an invoice.
     *
     * @throws AuthenticationException
     * @throws NotFoundException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function getUbl(string $salesInvoiceId): string
    {
        $response = $this->connector->send(new GetSalesInvoiceUblRequest(
            $this->connector->getCompanyId(),
            $salesInvoiceId
        ));

        $this->throwIfError($response, $this->notFoundFactory('Sales invoice', $salesInvoiceId));

        return $response->body();
    }

    /**
     * @param  array<string, mixed>  $invoice
     * @return array<string, mixed>
     */
    private function normalizeInvoicePayload(array $invoice): array
    {
        if (! isset($invoice['lines']) || ! is_array($invoice['lines'])) {
            return $invoice;
        }

        $lines = array_map(
            static function (mixed $line): array {
                if ($line instanceof InvoiceLine) {
                    return $line->toArray();
                }

                if (is_array($line)) {
                    return InvoiceLine::fromArray($line)->toArray();
                }

                return [];
            },
            $invoice['lines']
        );

        $invoice['lines'] = array_values(
            array_filter(
                $lines,
                static fn (array $line): bool => $line !== []
            )
        );

        return $invoice;
    }

    private function notFoundFactory(string $resource, string $identifier): Closure
    {
        return static fn (): NotFoundException => NotFoundException::resource($resource, $identifier);
    }
}
