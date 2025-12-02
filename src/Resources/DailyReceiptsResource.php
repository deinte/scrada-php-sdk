<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources;

use Deinte\ScradaSdk\Dto\AddDailyReceiptLinesData;
use Deinte\ScradaSdk\Dto\DailyReceiptLine;
use Deinte\ScradaSdk\Dto\PaymentMethod;
use Deinte\ScradaSdk\Exceptions\AuthenticationException;
use Deinte\ScradaSdk\Exceptions\ScradaException;
use Deinte\ScradaSdk\Exceptions\ValidationException;
use Deinte\ScradaSdk\Requests\DailyReceipts\AddDailyReceiptLinesRequest;
use Deinte\ScradaSdk\Requests\DailyReceipts\GetPaymentMethodsRequest;
use Deinte\ScradaSdk\Resources\Concerns\HandlesResponseErrors;
use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Http\BaseResource;

/**
 * Daily receipts endpoints.
 *
 * @property ScradaConnector $connector
 */
final class DailyReceiptsResource extends BaseResource
{
    use HandlesResponseErrors;

    /**
     * Retrieve configured payment methods for a daily receipts journal.
     *
     * @return array<int, PaymentMethod>
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function getPaymentMethods(string $journalId): array
    {
        $response = $this->connector->send(new GetPaymentMethodsRequest(
            $this->connector->getCompanyId(),
            $journalId
        ));

        $this->throwIfError($response);

        $data = $response->json();

        if (! is_array($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                $result[] = PaymentMethod::fromArray($item);
            }
        }

        return $result;
    }

    /**
     * Add lines to a daily receipts journal.
     *
     * @param  array<string, mixed>|AddDailyReceiptLinesData  $payload
     *
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws ScradaException
     */
    public function addLines(string $journalId, array|AddDailyReceiptLinesData $payload): void
    {
        $data = $payload instanceof AddDailyReceiptLinesData
            ? $payload->toArray()
            : $this->normalizePayload($payload);

        $response = $this->connector->send(new AddDailyReceiptLinesRequest(
            $this->connector->getCompanyId(),
            $journalId,
            $data
        ));

        $this->throwIfError($response);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        if (! isset($payload['lines']) || ! is_array($payload['lines'])) {
            return $payload;
        }

        $lines = array_map(
            static function (mixed $line): array {
                if ($line instanceof DailyReceiptLine) {
                    return $line->toArray();
                }

                if (is_array($line)) {
                    return DailyReceiptLine::fromArray($line)->toArray();
                }

                return [];
            },
            $payload['lines']
        );

        $payload['lines'] = array_values(
            array_filter(
                $lines,
                static fn (array $line): bool => $line !== []
            )
        );

        return $payload;
    }
}
