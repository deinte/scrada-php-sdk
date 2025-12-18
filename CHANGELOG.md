# Changelog

## 0.0.2 - 2025-12-18

### Changed
- **BREAKING**: Reorganized Data namespace into sub-namespaces for better organization:
  - `Deinte\ScradaSdk\Data\Common` - Shared DTOs (Address, Attachment, Customer)
  - `Deinte\ScradaSdk\Data\SalesInvoice` - Invoice DTOs (CreateSalesInvoiceData, CreateSalesInvoiceResponse, InvoiceLine, InvoicePaymentMethod, SalesInvoice, SendStatusResponse)
- **BREAKING**: `SalesInvoiceResource::create()` now only accepts `CreateSalesInvoiceData` (removed `array` and `SalesInvoice` input types)
- **BREAKING**: `PeppolResource::lookupParty()` now only accepts `Customer` (removed `array` input type)
- **BREAKING**: `InvoiceLine::$vatType` is now a `VatType` enum instead of `int`
- **BREAKING**: `Attachment::$fileType` is now a `FileType` enum instead of `int`
- **BREAKING**: Renamed `SendStatus` DTO to `SendStatusResponse` with complete API fields:
  - Added all Peppol-related fields (peppolSenderID, peppolReceiverID, peppolC2Timestamp, etc.)
  - Added email-related fields (receiverEmailAddress, receiverEmailTime, receiverEmailStatus)
  - Added `status` as `SendStatus` enum and `sendMethod` as `SendMethod` enum
  - Added helper methods: `isSuccess()`, `isError()`, `isPending()`, `wasSentViaPeppol()`, `wasSentViaEmail()`
- DTOs now use proper enums instead of magic integers for type safety

### Added
- `SendStatus` enum with all possible status values:
  - Peppol/Full subscription: Created, Processed, Retry, Canceled, Error, Error already sent, Error not on Peppol
  - Full subscription only: Blocked - send by email, Not on Peppol - send by email, Error - send by email, Blocked, None
  - Helper methods: `isSuccess()`, `isError()`, `isPending()`
- `SendMethod` enum for invoice sending methods:
  - Peppol, Email, Peppol and email, None
  - Helper methods: `usesPeppol()`, `usesEmail()`
- `FileType` enum for attachments (PDF, IMAGE, XML)
- `UnitType` enum for invoice line units (UNIT, PIECE, HOUR, KILOGRAM, etc. - 20 unit types)
- `ItemIdentificationType` enum for standard item identifiers (GLN, GTIN, GS1, etc.)
- `InvoicePaymentMethod` DTO for invoice payment methods (replaces raw arrays)
- `CreateSalesInvoiceData::$payableRoundingAmount` - optional rounding amount field
- `CreateSalesInvoiceData::$note` - optional note field
- `InvoiceLine::$unitType` - optional unit type field (nullable, defaults to UNIT)

### Fixed
- Improved type safety by enforcing DTO-only inputs on resource methods
- Better PHPStan compliance with proper enum types
- `SendStatusResponse` now includes complete API response (was missing most fields)

## 0.0.1 - 2025-12-02

### Changed
- **BREAKING**: `PeppolLookupResult` now uses Scrada API field names directly
  - `canReceiveInvoices` property → `supportInvoice` property
  - `canReceiveCreditNotes` property → `supportCreditInvoice` property
  - Removed `canReceiveOrders`, `canReceiveOrderResponses`, `canReceiveDespatchAdvice` properties
  - Added `registered`, `supportSelfBillingInvoice`, `supportSelfBillingCreditInvoice` properties
  - Added `canReceiveInvoices()` method (checks `registered && supportInvoice`)
  - Added `canReceiveCreditInvoices()` method (checks `registered && supportCreditInvoice`)

### Fixed
- Fixed PEPPOL lookup not recognizing registered companies due to incorrect field mapping

## 0.1.0 - 2025-11-13

- initial Scrada SDK implementation
- Saloon connector, resources, requests and DTOs
- PHPUnit + Pest test suite with architecture rules
- PHPStan level 9 configuration and developer tooling
