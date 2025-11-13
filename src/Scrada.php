<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk;

use Deinte\ScradaSdk\Resources\DailyReceiptsResource;
use Deinte\ScradaSdk\Resources\InboundDocumentResource;
use Deinte\ScradaSdk\Resources\PeppolResource;
use Deinte\ScradaSdk\Resources\SalesInvoiceResource;

/**
 * Entry point for interacting with the Scrada API.
 */
final class Scrada
{
    private readonly ScradaConnector $connector;

    /**
     * Bootstrap a new Scrada SDK instance.
     *
     * @param  non-empty-string  $apiKey
     * @param  non-empty-string  $apiSecret
     * @param  non-empty-string  $companyId
     * @param  non-empty-string|null  $baseUrl
     */
    public function __construct(
        string $apiKey,
        string $apiSecret,
        string $companyId,
        ?string $baseUrl = null,
    ) {
        $resolvedBaseUrl = $baseUrl ?? 'https://api.scrada.be';

        if ($resolvedBaseUrl === '') {
            $resolvedBaseUrl = 'https://api.scrada.be';
        }

        $this->connector = new ScradaConnector(
            apiKey: $apiKey,
            apiSecret: $apiSecret,
            companyId: $companyId,
            baseUrl: $resolvedBaseUrl,
        );
    }

    /**
     * Access the daily receipts resource.
     */
    public function dailyReceipts(): DailyReceiptsResource
    {
        return new DailyReceiptsResource($this->connector);
    }

    /**
     * Access the sales invoices resource.
     */
    public function salesInvoices(): SalesInvoiceResource
    {
        return new SalesInvoiceResource($this->connector);
    }

    /**
     * Access the Peppol resource.
     */
    public function peppol(): PeppolResource
    {
        return new PeppolResource($this->connector);
    }

    /**
     * Access the inbound documents resource.
     */
    public function inboundDocuments(): InboundDocumentResource
    {
        return new InboundDocumentResource($this->connector);
    }
}
