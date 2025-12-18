<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\DailyReceipts;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Get payment methods for a journal.
 */
final class GetPaymentMethodsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $companyId,
        private readonly string $journalId,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return sprintf('/v1/company/%s/journal/%s/paymentMethod', $this->companyId, $this->journalId);
    }
}
