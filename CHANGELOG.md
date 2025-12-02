# Changelog

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
