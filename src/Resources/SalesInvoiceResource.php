<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources;

use Closure;
use Deinte\ScradaSdk\Dto\CreateSalesInvoiceData;
use Deinte\ScradaSdk\Dto\CreateSalesInvoiceResponse;
use Deinte\ScradaSdk\Dto\InvoiceLine;
use Deinte\ScradaSdk\Dto\SalesInvoice;
use Deinte\ScradaSdk\Dto\SendStatus;
use Deinte\ScradaSdk\Exceptions\NotFoundException;
use Deinte\ScradaSdk\Requests\SalesInvoices\CreateSalesInvoiceRequest;
use Deinte\ScradaSdk\Requests\SalesInvoices\GetSalesInvoiceSendStatusRequest;
use Deinte\ScradaSdk\Requests\SalesInvoices\GetSalesInvoiceUblRequest;
use Deinte\ScradaSdk\Resources\Concerns\HandlesResponseErrors;
use Saloon\Http\BaseResource;

/**
 * Sales invoice operations.
 */
final class SalesInvoiceResource extends BaseResource
{
    use HandlesResponseErrors;

    /**
     * Create a new sales invoice.
     *
     * @param  array<string, mixed>|CreateSalesInvoiceData|SalesInvoice  $invoice
     */
    public function create(array|CreateSalesInvoiceData|SalesInvoice $invoice): CreateSalesInvoiceResponse
    {
        $payload = match (true) {
            $invoice instanceof CreateSalesInvoiceData => $invoice->toArray(),
            $invoice instanceof SalesInvoice => $invoice->toArray(),
            default => $this->normalizeInvoicePayload($invoice),
        };

        $response = $this->connector->send(new CreateSalesInvoiceRequest($payload));

        $this->throwIfError($response);

        $data = $response->json();

        if (! is_array($data)) {
            return new CreateSalesInvoiceResponse('', 'draft');
        }

        return CreateSalesInvoiceResponse::fromArray($data);
    }

    /**
     * Retrieve the send status of an invoice.
     */
    public function getSendStatus(string $salesInvoiceId): SendStatus
    {
        $response = $this->connector->send(new GetSalesInvoiceSendStatusRequest($salesInvoiceId));

        $this->throwIfError($response, $this->notFoundFactory('Sales invoice', $salesInvoiceId));

        $data = $response->json();

        if (! is_array($data)) {
            return new SendStatus('', false, false, false);
        }

        return SendStatus::fromArray($data);
    }

    /**
     * Retrieve the UBL XML for an invoice.
     */
    public function getUbl(string $salesInvoiceId): string
    {
        $response = $this->connector->send(new GetSalesInvoiceUblRequest($salesInvoiceId));

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
