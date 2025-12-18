<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\SalesInvoices;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Retrieve the UBL XML for an invoice.
 */
final class GetSalesInvoiceUblRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $companyId,
        private readonly string $salesInvoiceId,
    ) {}

    public function resolveEndpoint(): string
    {
        return sprintf('/v1/company/%s/salesInvoice/%s/ubl', $this->companyId, $this->salesInvoiceId);
    }
}
