# Scrada PHP SDK

[![Tests](https://github.com/deinte/scrada-php-sdk/actions/workflows/run-tests.yml/badge.svg)](https://github.com/deinte/scrada-php-sdk/actions/workflows/run-tests.yml)
[![PHP Version](https://img.shields.io/packagist/php-v/deinte/scrada-php-sdk)](https://packagist.org/packages/deinte/scrada-php-sdk)
[![License](https://img.shields.io/packagist/l/deinte/scrada-php-sdk)](LICENSE.md)

A PHP SDK for the [Scrada](https://www.scrada.be) accounting API with Peppol support, built on [Saloon v3](https://docs.saloon.dev).

> **Note:** This package was co-authored with AI assistance. Not all API endpoints have been tested in production. Please test thoroughly before use.

## Features

- Sales invoices with Peppol dispatch
- Peppol participant lookup
- Inbound document processing
- Daily receipts management
- Typed DTOs for all payloads
- PHPStan level 9

## Installation

```bash
composer require deinte/scrada-php-sdk
```

## Configuration

```env
SCRADA_API_KEY=your-api-key
SCRADA_API_SECRET=your-api-secret
SCRADA_COMPANY_ID=your-company-uuid
SCRADA_BASE_URL=https://apitest.scrada.be  # optional, defaults to production
```

## Quick Start

```php
use Deinte\ScradaSdk\Scrada;

$scrada = new Scrada(
    apiKey: $_ENV['SCRADA_API_KEY'],
    apiSecret: $_ENV['SCRADA_API_SECRET'],
    companyId: $_ENV['SCRADA_COMPANY_ID'],
);

// Create a sales invoice
$invoice = $scrada->salesInvoices()->create([
    'bookYear' => '2025',
    'journal' => 'SALES',
    'number' => '2025-001',
    'creditInvoice' => false,
    'invoiceDate' => '2025-01-15',
    'invoiceExpiryDate' => '2025-02-15',
    'totalInclVat' => 121.00,
    'totalExclVat' => 100.00,
    'totalVat' => 21.00,
    'customer' => [
        'code' => 'CUST01',
        'name' => 'Acme Corp',
        'vatNumber' => 'BE0123456789',
        'email' => 'billing@acme.com',
        'address' => [
            'street' => 'Main Street',
            'streetNumber' => '1',
            'city' => 'Brussels',
            'zipCode' => '1000',
            'countryCode' => 'BE',
        ],
    ],
    'lines' => [
        [
            'description' => 'Consulting services',
            'quantity' => 1,
            'unitPrice' => 100.00,
            'vatPerc' => 21,
        ],
    ],
]);

// Check Peppol send status
$status = $scrada->salesInvoices()->getSendStatus($invoice->id);

// Lookup Peppol participant
$participant = $scrada->peppol()->lookupParty([
    'vatNumber' => 'BE0123456789',
    'countryCode' => 'BE',
]);

// Process inbound documents
$documents = $scrada->inboundDocuments()->getUnconfirmed();
foreach ($documents as $document) {
    $pdf = $scrada->inboundDocuments()->getPdf($document->id);
    $scrada->inboundDocuments()->confirm($document->id);
}
```

## Testing

```bash
composer test      # Run tests
composer analyse   # PHPStan
composer format    # Laravel Pint
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.
