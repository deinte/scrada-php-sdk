<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\SalesInvoices;

use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Retrieve the UBL XML for an invoice.
 */
final class GetSalesInvoiceUblRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $salesInvoiceId,
    ) {}

    public function resolveEndpoint(): string
    {
        /** @var ScradaConnector $connector */
        $connector = $this->connector;
        $companyId = $connector->getCompanyId();

        return sprintf('/v1/company/%s/salesInvoice/%s/ubl', $companyId, $this->salesInvoiceId);
    }
}
