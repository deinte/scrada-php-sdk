<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\SalesInvoices;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Create a sales invoice.
 */
final class CreateSalesInvoiceRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly string $companyId,
        private readonly array $payload,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return sprintf('/v1/company/%s/salesInvoice', $this->companyId);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
