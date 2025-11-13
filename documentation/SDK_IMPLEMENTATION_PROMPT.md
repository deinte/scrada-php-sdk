# Scrada PHP SDK - Complete Implementation Prompt

## Project Overview
Build a professional, production-ready PHP SDK for the Scrada API (https://www.scrada.be), a Peppol-enabled invoicing and accounting tool. The SDK must follow the architecture patterns established by the Oh Dear PHP SDK, using Saloon v3 for HTTP communication.

## Package Configuration
- **Package name**: `deinte/scrada-php-sdk`
- **Namespace**: `Deinte\ScradaSdk`
- **PHP Version**: 8.3+
- **Working Directory**: `/Users/danteschrauwen/Documents/Projecten/Salino/scrada-php-sdk/`
- **API Documentation**: Postman collection in `documentation/Scrada Full.postman_collection.json`

## Code Quality Requirements

### Strict Code Standards
1. **PSR-12 compliance** - strictly enforce PSR-12 coding standards
2. **No `else` statements** - use early returns and guard clauses exclusively
3. **Defensive programming** - add comprehensive validation and null checks
4. **Clear error messages** - exceptions must be descriptive and include context
5. **String formatting**:
   - Prefer string interpolation: `"User {$name} not found"`
   - Use `sprintf()` when interpolation isn't possible or variables exist solely for string building
6. **Type safety**:
   - Proper type hints on all parameters, return types, and properties
   - PHP 8.3 features where beneficial
   - No mixed types unless absolutely necessary
7. **Documentation**:
   - PHPDoc blocks on all public methods
   - Type hints in signatures (no redundant `@param` for typed parameters)
   - Clear examples in class-level documentation

### Static Analysis & Testing
- **PHPStan**: Level 9 (maximum strictness)
- **PHPUnit**: Comprehensive unit and feature tests
- **Architecture tests**: Using Pest for architectural constraints
- **Code coverage**: Aim for high coverage on critical paths

## Authentication & Configuration

### API Authentication
```php
// Headers required for all requests
'X-API-KEY' => 'your-api-key'
'X-PASSWORD' => 'your-api-secret'
'Content-Type' => 'application/json'
```

### Base URLs
- **Test Environment**: `https://apitest.scrada.be`
- **Production**: `https://api.scrada.be` (assumed)
- **API Version**: All endpoints use `/v1` prefix
- **Company Context**: Most endpoints require `companyID` in path

### Environment Variables
```env
SCRADA_API_KEY=your-api-key
SCRADA_API_SECRET=your-api-secret
SCRADA_COMPANY_ID=your-company-uuid
SCRADA_BASE_URL=https://apitest.scrada.be  # optional, defaults to production
```

## SDK Architecture Pattern

### Core Design (Oh Dear Style)
```php
// Initialization
$scrada = new Scrada(
    apiKey: 'your-api-key',
    apiSecret: 'your-api-secret',
    companyId: 'company-uuid',
    baseUrl: 'https://apitest.scrada.be' // optional
);

// Resource-oriented fluent API
$paymentMethods = $scrada->dailyReceipts()->getPaymentMethods($journalId);
$scrada->dailyReceipts()->addLines($journalId, $data);

$invoice = $scrada->salesInvoices()->create($invoiceData);
$status = $scrada->salesInvoices()->getSendStatus($invoiceId);
$ubl = $scrada->salesInvoices()->getUbl($invoiceId);

$peppolLookup = $scrada->peppol()->lookupParty($partyData);

$documents = $scrada->inboundDocuments()->getUnconfirmed();
$document = $scrada->inboundDocuments()->get($documentId);
$pdf = $scrada->inboundDocuments()->getPdf($documentId);
$scrada->inboundDocuments()->confirm($documentId);
```

## Project Structure

```
scrada-php-sdk/
├── documentation/
│   ├── Scrada Full.postman_collection.json
│   └── SDK_IMPLEMENTATION_PROMPT.md
├── src/
│   ├── Scrada.php                          # Main client class
│   ├── ScradaConnector.php                 # Saloon connector with auth
│   │
│   ├── Resources/                          # Resource classes (fluent API)
│   │   ├── DailyReceiptsResource.php
│   │   ├── SalesInvoiceResource.php
│   │   ├── PeppolResource.php
│   │   └── InboundDocumentResource.php
│   │
│   ├── Requests/                           # Saloon request classes
│   │   ├── DailyReceipts/
│   │   │   ├── GetPaymentMethodsRequest.php
│   │   │   └── AddDailyReceiptLinesRequest.php
│   │   │
│   │   ├── SalesInvoices/
│   │   │   ├── CreateSalesInvoiceRequest.php
│   │   │   ├── GetSalesInvoiceSendStatusRequest.php
│   │   │   └── GetSalesInvoiceUblRequest.php
│   │   │
│   │   ├── Peppol/
│   │   │   └── LookupPartyRequest.php
│   │   │
│   │   └── InboundDocuments/
│   │       ├── GetUnconfirmedDocumentsRequest.php
│   │       ├── GetDocumentRequest.php
│   │       ├── GetDocumentPdfRequest.php
│   │       └── ConfirmDocumentRequest.php
│   │
│   ├── Dto/                                # Data Transfer Objects
│   │   ├── PaymentMethod.php
│   │   ├── DailyReceiptLine.php
│   │   ├── SalesInvoice.php
│   │   ├── Customer.php
│   │   ├── Address.php
│   │   ├── InvoiceLine.php
│   │   ├── SendStatus.php
│   │   ├── PeppolLookupResult.php
│   │   ├── InboundDocument.php
│   │   └── ...
│   │
│   └── Exceptions/
│       ├── ScradaException.php             # Base exception
│       ├── ValidationException.php         # Validation errors with field details
│       ├── AuthenticationException.php     # Auth failures
│       └── NotFoundException.php           # 404 errors
│
├── tests/
│   ├── Unit/                              # Unit tests for DTOs, utilities
│   ├── Feature/                           # Integration tests with VCR/mocking
│   ├── ArchTest.php                       # Pest architecture tests
│   └── Pest.php                           # Pest configuration
│
├── composer.json
├── phpunit.xml.dist
├── phpstan.neon
├── README.md
└── CHANGELOG.md
```

## Complete API Endpoint Mapping

### 1. Daily Receipts Resource (2 endpoints)

#### Get Payment Methods
```
GET /v1/company/{companyID}/journal/{dailyReceiptsJournalID}/paymentMethod
Response: Array of payment methods with ID, name, type
```

#### Add Daily Receipt Lines
```
PUT /v1/company/{companyID}/journal/{dailyReceiptsJournalID}/lines
Body: {
  "date": "2021-07-30",
  "lines": [
    {
      "lineType": 1,
      "vatTypeID": "uuid",
      "vatPerc": 21,
      "amount": 500,
      "categoryID": "uuid"
    }
  ],
  "paymentMethods": [
    {
      "paymentMethodID": "uuid",
      "amount": 200
    }
  ]
}
```

### 2. Sales Invoices Resource (4 endpoints + 1 deprecated)

#### Peppol Lookup Party
```
POST /v1/company/{companyID}/peppol/lookup
Body: {
  "code": "CUST01",
  "name": "Customer 01",
  "address": {
    "street": "...",
    "streetNumber": "1",
    "city": "...",
    "zipCode": "1000",
    "countryCode": "BE"
  },
  "email": "...",
  "vatNumber": "BE0793904121"
}
Response: Boolean flags for each document type (invoice, credit note, etc.)
```

#### Create Sales Invoice
```
POST /v1/company/{companyID}/salesInvoice
Body: {
  "bookYear": "2025",
  "journal": "Store1",
  "number": "133",
  "creditInvoice": false,
  "invoiceDate": "2025-06-03",
  "invoiceExpiryDate": "2025-06-30",
  "customer": { ... },
  "totalInclVat": 200.00,
  "totalExclVat": 165.29,
  "totalVat": 34.71,
  "lines": [ ... ],
  "alreadySendToCustomer": false  // optional
}
Response: Invoice ID and status
```

#### Get Sales Invoice Send Status
```
GET /v1/company/{companyID}/salesInvoice/{salesInvoiceID}/sendStatus
Response: Status of document (sent via Peppol, email, pending, etc.)
```

#### Get Sales Invoice UBL
```
GET /v1/company/{companyID}/salesInvoice/{salesInvoiceID}/ubl
Response: UBL XML document sent to customer (only available after Peppol send)
```

#### Add Invoice (DEPRECATED - backward compatibility only)
```
POST /v1/company/{companyID}/invoice
Note: Still supported but use POST /salesInvoice instead
```

### 3. Inbound Documents Resource (4 endpoints)

#### Get Unconfirmed Documents
```
GET /v1/company/{companyID}/peppol/inbound/document/unconfirmed
Response: Array of document IDs and metadata for documents not yet confirmed
```

#### Get Document
```
GET /v1/company/{companyID}/peppol/inbound/document/{inboundDocumentID}
Response: Full document data in JSON format
```

#### Get Document as PDF
```
GET /v1/company/{companyID}/peppol/inbound/document/{inboundDocumentID}/pdf
Response: PDF binary data (purchase invoices formatted, other UBL in PDF wrapper)
```

#### Confirm Document
```
PUT /v1/company/{companyID}/peppol/inbound/document/{inboundDocumentID}/confirm
Response: Confirmation status
Note: Must be called after downloading to mark as processed
```

## Implementation Details

### 1. Main Client Class

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk;

use Deinte\ScradaSdk\Resources\DailyReceiptsResource;
use Deinte\ScradaSdk\Resources\SalesInvoiceResource;
use Deinte\ScradaSdk\Resources\PeppolResource;
use Deinte\ScradaSdk\Resources\InboundDocumentResource;

final readonly class Scrada
{
    private ScradaConnector $connector;

    public function __construct(
        string $apiKey,
        string $apiSecret,
        string $companyId,
        ?string $baseUrl = null,
    ) {
        $this->connector = new ScradaConnector(
            apiKey: $apiKey,
            apiSecret: $apiSecret,
            companyId: $companyId,
            baseUrl: $baseUrl ?? 'https://api.scrada.be',
        );
    }

    public function dailyReceipts(): DailyReceiptsResource
    {
        return new DailyReceiptsResource($this->connector);
    }

    public function salesInvoices(): SalesInvoiceResource
    {
        return new SalesInvoiceResource($this->connector);
    }

    public function peppol(): PeppolResource
    {
        return new PeppolResource($this->connector);
    }

    public function inboundDocuments(): InboundDocumentResource
    {
        return new InboundDocumentResource($this->connector);
    }
}
```

### 2. Connector Class

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

final class ScradaConnector extends Connector
{
    use AcceptsJson;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private readonly string $companyId,
        private readonly string $baseUrl = 'https://api.scrada.be',
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function defaultHeaders(): array
    {
        return [
            'X-API-KEY' => $this->apiKey,
            'X-PASSWORD' => $this->apiSecret,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function getCompanyId(): string
    {
        return $this->companyId;
    }
}
```

### 3. Resource Class Pattern

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Resources;

use Deinte\ScradaSdk\Dto\PaymentMethod;
use Deinte\ScradaSdk\Requests\DailyReceipts\GetPaymentMethodsRequest;
use Deinte\ScradaSdk\Requests\DailyReceipts\AddDailyReceiptLinesRequest;
use Deinte\ScradaSdk\Exceptions\ScradaException;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

final class DailyReceiptsResource extends BaseResource
{
    /**
     * Get all payment methods for a daily receipts journal.
     *
     * @param string $journalId The daily receipts journal ID
     * @return array<PaymentMethod>
     * @throws ScradaException
     */
    public function getPaymentMethods(string $journalId): array
    {
        $response = $this->connector->send(
            new GetPaymentMethodsRequest($journalId)
        );

        if ($response->failed()) {
            throw ScradaException::fromResponse($response);
        }

        $data = $response->json();

        if (!is_array($data)) {
            return [];
        }

        return array_map(
            fn (array $item) => PaymentMethod::fromArray($item),
            $data
        );
    }

    /**
     * Add daily receipt lines to a journal.
     *
     * @param string $journalId The daily receipts journal ID
     * @param array<string, mixed> $data Receipt line data
     * @throws ScradaException
     */
    public function addLines(string $journalId, array $data): void
    {
        $response = $this->connector->send(
            new AddDailyReceiptLinesRequest($journalId, $data)
        );

        if ($response->failed()) {
            throw ScradaException::fromResponse($response);
        }
    }
}
```

### 4. Request Class Pattern

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\DailyReceipts;

use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Enums\Method;
use Saloon\Http\Request;

final class GetPaymentMethodsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $journalId,
    ) {}

    public function resolveEndpoint(): string
    {
        /** @var ScradaConnector $connector */
        $connector = $this->connector;
        $companyId = $connector->getCompanyId();

        return sprintf(
            '/v1/company/%s/journal/%s/paymentMethod',
            $companyId,
            $this->journalId
        );
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Requests\DailyReceipts;

use Deinte\ScradaSdk\ScradaConnector;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class AddDailyReceiptLinesRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    /**
     * @param string $journalId
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly string $journalId,
        private readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        /** @var ScradaConnector $connector */
        $connector = $this->connector;
        $companyId = $connector->getCompanyId();

        return sprintf(
            '/v1/company/%s/journal/%s/lines',
            $companyId,
            $this->journalId
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return $this->data;
    }
}
```

### 5. DTO Pattern

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

final readonly class PaymentMethod
{
    public function __construct(
        public string $id,
        public string $name,
        public int $type,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            name: $data['name'] ?? '',
            type: (int) ($data['type'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

final readonly class Address
{
    public function __construct(
        public string $street,
        public string $streetNumber,
        public string $city,
        public string $zipCode,
        public string $countryCode,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? '',
            streetNumber: $data['streetNumber'] ?? '',
            city: $data['city'] ?? '',
            zipCode: $data['zipCode'] ?? '',
            countryCode: $data['countryCode'] ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'streetNumber' => $this->streetNumber,
            'city' => $this->city,
            'zipCode' => $this->zipCode,
            'countryCode' => $this->countryCode,
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

final readonly class Customer
{
    public function __construct(
        public string $code,
        public string $name,
        public Address $address,
        public string $email,
        public string $vatNumber,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? '',
            name: $data['name'] ?? '',
            address: Address::fromArray($data['address'] ?? []),
            email: $data['email'] ?? '',
            vatNumber: $data['vatNumber'] ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address->toArray(),
            'email' => $this->email,
            'vatNumber' => $this->vatNumber,
        ];
    }
}
```

### 6. Exception Handling

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

use Exception;
use Saloon\Http\Response;

class ScradaException extends Exception
{
    public static function fromResponse(Response $response): self
    {
        $data = $response->json();
        $message = is_array($data) && isset($data['message'])
            ? $data['message']
            : 'Unknown Scrada API error';

        $fullMessage = sprintf(
            'Scrada API error: %s (HTTP %d)',
            $message,
            $response->status()
        );

        return new self($fullMessage, $response->status());
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

final class ValidationException extends ScradaException
{
    /**
     * @param array<string, array<string>> $errors
     */
    public function __construct(
        string $message,
        private readonly array $errors = [],
        int $code = 422,
    ) {
        parent::__construct($message, $code);
    }

    /**
     * @return array<string, array<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public static function fromResponse(Response $response): self
    {
        $data = $response->json();

        $message = is_array($data) && isset($data['message'])
            ? $data['message']
            : 'Validation failed';

        $errors = is_array($data) && isset($data['errors']) && is_array($data['errors'])
            ? $data['errors']
            : [];

        return new self($message, $errors, $response->status());
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

final class AuthenticationException extends ScradaException
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid API credentials provided', 401);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Exceptions;

final class NotFoundException extends ScradaException
{
    public static function resource(string $type, string $id): self
    {
        return new self(
            sprintf('%s with ID %s not found', $type, $id),
            404
        );
    }
}
```

## Error Handling Strategy

### Defensive Programming
- Validate all inputs before making API calls
- Check array keys exist before accessing
- Type-cast values from API responses
- Handle null values gracefully
- Provide default values where sensible

### Clear Exception Messages
```php
// Bad
throw new Exception('Error');

// Good
throw NotFoundException::resource('SalesInvoice', $invoiceId);

// Good
throw new ScradaException(
    sprintf(
        'Failed to create sales invoice %s for customer %s: %s',
        $invoiceNumber,
        $customerCode,
        $errorMessage
    )
);
```

### Response Error Handling
```php
$response = $this->connector->send($request);

// Check for specific error types
if ($response->status() === 401) {
    throw AuthenticationException::invalidCredentials();
}

if ($response->status() === 404) {
    throw NotFoundException::resource('Document', $documentId);
}

if ($response->status() === 422) {
    throw ValidationException::fromResponse($response);
}

if ($response->failed()) {
    throw ScradaException::fromResponse($response);
}

// Proceed with successful response
return $this->parseResponse($response);
```

## Testing Requirements

### PHPUnit Configuration
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory>src</directory>
        </include>
    </coverage>
</phpunit>
```

### Architecture Tests (Pest)
```php
<?php

arch('all classes are final')
    ->expect('Deinte\ScradaSdk')
    ->classes()
    ->toBeFinal();

arch('no else statements')
    ->expect('Deinte\ScradaSdk')
    ->not->toUse('else');

arch('exceptions extend base exception')
    ->expect('Deinte\ScradaSdk\Exceptions')
    ->toExtend(ScradaException::class)
    ->ignoring(ScradaException::class);

arch('DTOs are readonly')
    ->expect('Deinte\ScradaSdk\Dto')
    ->classes()
    ->toBeReadonly();

arch('requests extend Saloon request')
    ->expect('Deinte\ScradaSdk\Requests')
    ->toExtend(Saloon\Http\Request::class);

arch('resources extend Saloon base resource')
    ->expect('Deinte\ScradaSdk\Resources')
    ->toExtend(Saloon\Http\BaseResource::class);
```

### Unit Test Examples
```php
<?php

use Deinte\ScradaSdk\Dto\Address;

it('creates address from array', function () {
    $data = [
        'street' => 'Main Street',
        'streetNumber' => '123',
        'city' => 'Brussels',
        'zipCode' => '1000',
        'countryCode' => 'BE',
    ];

    $address = Address::fromArray($data);

    expect($address->street)->toBe('Main Street')
        ->and($address->streetNumber)->toBe('123')
        ->and($address->city)->toBe('Brussels')
        ->and($address->zipCode)->toBe('1000')
        ->and($address->countryCode)->toBe('BE');
});

it('handles missing address fields gracefully', function () {
    $address = Address::fromArray([]);

    expect($address->street)->toBe('')
        ->and($address->city)->toBe('');
});
```

### Feature Test Examples
```php
<?php

use Deinte\ScradaSdk\Scrada;

it('retrieves payment methods', function () {
    $scrada = new Scrada(
        apiKey: 'test-key',
        apiSecret: 'test-secret',
        companyId: 'test-company-id',
        baseUrl: 'https://apitest.scrada.be'
    );

    $methods = $scrada->dailyReceipts()->getPaymentMethods('journal-id');

    expect($methods)->toBeArray()
        ->and($methods)->each->toBeInstanceOf(PaymentMethod::class);
});
```

## composer.json

```json
{
    "name": "deinte/scrada-php-sdk",
    "description": "PHP SDK for the Scrada API - Peppol-enabled invoicing and accounting",
    "keywords": [
        "scrada",
        "peppol",
        "invoicing",
        "accounting",
        "sdk",
        "api"
    ],
    "homepage": "https://github.com/deinte/scrada-php-sdk",
    "license": "MIT",
    "authors": [
        {
            "name": "Deinte",
            "role": "Developer"
        }
    ],
    "require": {
        "php": "^8.3",
        "saloon/saloon": "^3.0"
    },
    "require-dev": {
        "pestphp/pest": "^2.0",
        "phpstan/phpstan": "^1.10",
        "phpunit/phpunit": "^10.0",
        "symfony/var-dumper": "^6.0|^7.0"
    },
    "autoload": {
        "psr-4": {
            "Deinte\\ScradaSdk\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Deinte\\ScradaSdk\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "pest",
        "test:unit": "pest --testsuite=Unit",
        "test:feature": "pest --testsuite=Feature",
        "analyse": "phpstan analyse",
        "format": "php-cs-fixer fix"
    },
    "config": {
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "phpstan/extension-installer": true
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

## PHPStan Configuration

```neon
includes:
    - vendor/phpstan/phpstan/conf/bleedingEdge.neon

parameters:
    level: 9
    paths:
        - src
    tmpDir: build/phpstan
    checkOctaneCompatibility: true
    checkModelProperties: true
    checkMissingIterableValueType: false
```

## README.md Structure

```markdown
# Scrada PHP SDK

Professional PHP SDK for the Scrada API - Peppol-enabled invoicing and accounting.

## Installation

```bash
composer require deinte/scrada-php-sdk
```

## Quick Start

```php
use Deinte\ScradaSdk\Scrada;

$scrada = new Scrada(
    apiKey: 'your-api-key',
    apiSecret: 'your-api-secret',
    companyId: 'your-company-id'
);

// Create a sales invoice
$invoice = $scrada->salesInvoices()->create([
    'bookYear' => '2025',
    'journal' => 'SALES',
    'number' => '2025-001',
    // ... other fields
]);

// Check Peppol status
$lookup = $scrada->peppol()->lookupParty([
    'vatNumber' => 'BE0123456789',
    // ... other fields
]);

// Get inbound documents
$documents = $scrada->inboundDocuments()->getUnconfirmed();
```

## Features

- ✅ Type-safe DTOs
- ✅ Comprehensive error handling
- ✅ PHPStan Level 9
- ✅ Full test coverage
- ✅ Fluent API design
- ✅ Production-ready

## Documentation

- [API Documentation](https://www.scrada.be/api-documentation/)
- [Full Usage Guide](docs/usage.md)
- [Examples](docs/examples.md)

## Testing

```bash
composer test
composer analyse
```

## License

MIT License
```

## Implementation Checklist

### Phase 1: Foundation
- [ ] Configure composer.json with correct namespace and dependencies
- [ ] Create base directory structure
- [ ] Implement ScradaConnector with authentication
- [ ] Implement main Scrada client class
- [ ] Set up PHPStan and PHPUnit configurations
- [ ] Create base exception classes

### Phase 2: Daily Receipts
- [ ] Create DailyReceiptsResource
- [ ] Implement GetPaymentMethodsRequest
- [ ] Implement AddDailyReceiptLinesRequest
- [ ] Create PaymentMethod DTO
- [ ] Create DailyReceiptLine DTO
- [ ] Write tests for daily receipts

### Phase 3: Sales Invoices
- [ ] Create SalesInvoiceResource
- [ ] Create PeppolResource (for lookup)
- [ ] Implement LookupPartyRequest
- [ ] Implement CreateSalesInvoiceRequest
- [ ] Implement GetSalesInvoiceSendStatusRequest
- [ ] Implement GetSalesInvoiceUblRequest
- [ ] Create SalesInvoice DTO
- [ ] Create Customer DTO
- [ ] Create Address DTO
- [ ] Create SendStatus DTO
- [ ] Create PeppolLookupResult DTO
- [ ] Write tests for sales invoices and Peppol

### Phase 4: Inbound Documents
- [ ] Create InboundDocumentResource
- [ ] Implement GetUnconfirmedDocumentsRequest
- [ ] Implement GetDocumentRequest
- [ ] Implement GetDocumentPdfRequest
- [ ] Implement ConfirmDocumentRequest
- [ ] Create InboundDocument DTO
- [ ] Write tests for inbound documents

### Phase 5: Quality & Documentation
- [ ] Run PHPStan Level 9 and fix all issues
- [ ] Write architecture tests
- [ ] Achieve test coverage targets
- [ ] Write comprehensive README
- [ ] Add code examples
- [ ] Update CHANGELOG

## Key Implementation Notes

1. **Company ID Context**: Most endpoints require `companyID` - store this in connector and access it in request classes
2. **UUIDs**: Many IDs are UUIDs - validate format where appropriate
3. **Date Formats**: Use ISO 8601 format (YYYY-MM-DD)
4. **Decimal Precision**: Use proper precision for monetary amounts
5. **VAT Numbers**: Format varies by country (e.g., BE0793904121)
6. **PDF Responses**: Handle binary data for PDF endpoints
7. **Webhook Integration**: Document webhook support (out of SDK scope, but document it)
8. **Deprecated Endpoints**: Include `/invoice` endpoint but mark as deprecated in docs

## Advanced Features (Future)

- [ ] Response caching
- [ ] Rate limit handling
- [ ] Retry logic with exponential backoff
- [ ] Webhook signature validation
- [ ] Batch operations helper
- [ ] Laravel service provider (separate package)
- [ ] Mock connector for testing

---

**This prompt provides everything needed to build a production-ready Scrada PHP SDK following best practices and the Oh Dear SDK architecture pattern.**
