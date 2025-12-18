<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources;

use Closure;
use Deinte\ScradaSdk\Data\SalesInvoice\CreateSalesInvoiceData;
use Deinte\ScradaSdk\Data\SalesInvoice\CreateSalesInvoiceResponse;
use Deinte\ScradaSdk\Data\SalesInvoice\SendStatusResponse;
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
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function create(CreateSalesInvoiceData $data): CreateSalesInvoiceResponse
    {
        $response = $this->connector->send(new CreateSalesInvoiceRequest(
            $this->connector->getCompanyId(),
            $data->toArray(),
        ));

        $this->throwIfError($response);

        $body = $response->body();
        $json = json_decode($body, true);

        // Scrada may return a simple string (invoice ID) or a JSON object
        if (! is_array($json)) {
            $invoiceId = is_string($json) ? $json : trim($body, '"');

            return new CreateSalesInvoiceResponse((string) $invoiceId, 'draft');
        }

        return CreateSalesInvoiceResponse::fromArray($json);
    }

    /**
     * Retrieve the send status of an invoice.
     *
     * @throws AuthenticationException
     * @throws NotFoundException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function getSendStatus(string $invoiceId): SendStatusResponse
    {
        $response = $this->connector->send(new GetSalesInvoiceSendStatusRequest(
            $this->connector->getCompanyId(),
            $invoiceId,
        ));

        $this->throwIfError($response, $this->notFoundFactory('Sales invoice', $invoiceId));

        $data = $response->json();

        if (! is_array($data)) {
            return SendStatusResponse::fromArray([]);
        }

        return SendStatusResponse::fromArray($data);
    }

    /**
     * Retrieve the UBL XML for an invoice.
     *
     * @throws AuthenticationException
     * @throws NotFoundException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function getUbl(string $invoiceId): string
    {
        $response = $this->connector->send(new GetSalesInvoiceUblRequest(
            $this->connector->getCompanyId(),
            $invoiceId,
        ));

        $this->throwIfError($response, $this->notFoundFactory('Sales invoice', $invoiceId));

        return $response->body();
    }

    private function notFoundFactory(string $resource, string $identifier): Closure
    {
        return static fn (): NotFoundException => NotFoundException::resource($resource, $identifier);
    }
}
