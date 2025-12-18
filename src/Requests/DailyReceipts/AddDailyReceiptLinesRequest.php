<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\DailyReceipts;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Add lines to a daily receipts journal.
 */
final class AddDailyReceiptLinesRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly string $companyId,
        private readonly string $journalId,
        private readonly array $payload,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return sprintf('/v1/company/%s/journal/%s/lines', $this->companyId, $this->journalId);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
