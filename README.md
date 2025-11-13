# Scrada PHP SDK

Production-ready SDK for the [Scrada](https://www.scrada.be) API with first-class Peppol support and a fluent Oh Dear–style developer experience.

## Highlights

- Built on [Saloon v3](https://docs.saloon.dev) with typed resources and requests
- Defensive programming, PSR-12, and zero `else` statements
- DTOs for every payload to guarantee consistent structures
- PHPUnit + Pest test suite with architectural rules and Saloon mocks
- PHPStan level 9 configuration out of the box

## Installation

```bash
composer require deinte/scrada-php-sdk
```

## Configuration

Set your Scrada credentials (for example in `.env`):

```dotenv
SCRADA_API_KEY=your-api-key
SCRADA_API_SECRET=your-api-secret
SCRADA_COMPANY_ID=your-company-uuid
SCRADA_BASE_URL=https://apitest.scrada.be # optional, defaults to production
```

## Usage

```php
use Deinte\ScradaSdk\Scrada;

$scrada = new Scrada(
    apiKey: $_ENV['SCRADA_API_KEY'],
    apiSecret: $_ENV['SCRADA_API_SECRET'],
    companyId: $_ENV['SCRADA_COMPANY_ID'],
    baseUrl: $_ENV['SCRADA_BASE_URL'] ?? null,
);

// Daily receipts
$paymentMethods = $scrada->dailyReceipts()->getPaymentMethods('journal-id');
$scrada->dailyReceipts()->addLines('journal-id', [
    'date' => '2025-06-05',
    'lines' => [
        [
            'lineType' => 1,
            'vatTypeID' => 'vat-type',
            'vatPerc' => 21,
            'amount' => 100,
        ],
    ],
    'paymentMethods' => [
        ['paymentMethodID' => 'pm-1', 'amount' => 100],
    ],
]);

// Sales invoices + Peppol
$invoice = $scrada->salesInvoices()->create([
    'bookYear' => '2025',
    'journal' => 'SALES',
    'number' => '2025-001',
    'creditInvoice' => false,
    'invoiceDate' => '2025-06-03',
    'invoiceExpiryDate' => '2025-06-30',
    'customer' => [
        'code' => 'CUST01',
        'name' => 'Customer',
        'email' => 'customer@example.com',
        'vatNumber' => 'BE0123456789',
        'address' => [
            'street' => 'Main Street',
            'streetNumber' => '1',
            'city' => 'Brussels',
            'zipCode' => '1000',
            'countryCode' => 'BE',
        ],
    ],
    'totalInclVat' => 121,
    'totalExclVat' => 100,
    'totalVat' => 21,
    'lines' => [
        [
            'description' => 'Service',
            'quantity' => 1,
            'unitPrice' => 100,
            'vatPerc' => 21,
            'vatTypeID' => 'vat-type',
        ],
    ],
]);

$status = $scrada->salesInvoices()->getSendStatus($invoice->id ?? 'invoice-id');
$ubl = $scrada->salesInvoices()->getUbl($invoice->id ?? 'invoice-id');
$peppol = $scrada->peppol()->lookupParty([
    'code' => 'CUST01',
    'name' => 'Customer',
    'vatNumber' => 'BE0123456789',
    'email' => 'customer@example.com',
    'address' => [
        'street' => 'Main Street',
        'streetNumber' => '1',
        'city' => 'Brussels',
        'zipCode' => '1000',
        'countryCode' => 'BE',
    ],
]);

// Inbound documents
$documents = $scrada->inboundDocuments()->getUnconfirmed();
foreach ($documents as $document) {
    $pdf = $scrada->inboundDocuments()->getPdf($document->id);
    $scrada->inboundDocuments()->confirm($document->id);
}
```

## Testing & Quality

```bash
composer test        # Runs Pest + PHPUnit (unit, feature, and architecture tests)
composer analyse     # PHPStan level 9
composer format      # Laravel Pint
composer test-coverage
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for release details.

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
