# SDK Way of Working

This document captures the conventions that shape `scrada-php-sdk`. Treat it as the single source of truth for how we structure, test, and extend the SDK so new changes stay predictable.

## Goals & Principles
- Ship a thin, typed wrapper around the Scrada API powered by Saloon resources and requests.
- Keep the public API expressive (DTOs, resources, fluent helpers) while hiding HTTP details in dedicated request objects.
- Guard correctness with deterministic unit/feature tests plus architectural rules that enforce final classes, readonly DTOs, and zero `else` statements.
- Make every change easy to reason about by funnelling HTTP error handling, authentication, and company scoping through shared helpers.

## Technology Stack
- **Language:** PHP 8.3+, strict types everywhere, readonly DTOs.
- **HTTP:** `saloonphp/saloon` v3.2 using `Connector`, `BaseResource`, JSON bodies, and manual request classes.
- **Testing:** Pest 2 + PHPUnit 10 with Saloon `MockClient`/`MockResponse` (Mockery is available for edge cases).
- **Static analysis:** PHPStan level 9 with a small baseline stored in `phpstan-baseline.neon`.
- **Formatting:** Laravel Pint (PSR-12 style, zero `else` statements enforced by Arch tests).

Composer scripts wrap the common tasks:

| Command | Purpose |
| --- | --- |
| `composer test` | Run the Pest suite (unit, feature, architecture). |
| `composer test-coverage` | Same as `test` but with coverage output. |
| `composer analyse` | PHPStan level 9 across `src/` and `tests/`. |
| `composer format` | Run Laravel Pint. |

## Repository Layout

| Path | Description |
| --- | --- |
| `src/Scrada.php` | Public entry point that wires credentials and exposes typed resources. |
| `src/ScradaConnector.php` | Saloon connector configuring auth headers, base URL resolution, and guard clauses. |
| `src/Resources/*.php` | Resource classes (`DailyReceiptsResource`, `SalesInvoiceResource`, `PeppolResource`, `InboundDocumentResource`) that encapsulate cohesive endpoints. |
| `src/Resources/Concerns/HandlesResponseErrors.php` | Shared trait that converts HTTP status codes into domain exceptions. |
| `src/Requests/<Domain>/` | Saloon request classes per endpoint (POST/GET definitions, JSON payloads, endpoints). |
| `src/Dto/` | Immutable data-transfer objects with `fromArray`/`toArray` helpers. |
| `src/Exceptions/` | SDK-specific exceptions (`ScradaException`, `ValidationException`, `AuthenticationException`, `NotFoundException`). |
| `tests/Unit/` | DTO and connector tests that validate parsing logic and guard clauses. |
| `tests/Feature/` | Resource tests backed by Saloon mocks to assert request wiring and response mapping. |
| `tests/ArchTest.php` | Architecture rules (final classes, readonly DTOs, `no else`, inheritance constraints, debug helpers). |
| `tests/Pest.php` | Registers `unit`/`feature` groups for the test suite. |
| `documentation/` | Reference material such as the Postman collection and implementation prompt. |

## HTTP Layer Design

### Base Connector
- `ScradaConnector` extends `Saloon\Http\Connector` and uses `AcceptsJson`. Credentials (`apiKey`, `apiSecret`, `companyId`) and the optional base URL are validated through private guard methods at construction time.
- `resolveBaseUrl()` returns either the passed base URL or defaults to `https://api.scrada.be`, trimming trailing slashes and rejecting malformed schemes.
- `defaultHeaders()` injects Scrada’s expected headers (`X-API-KEY`, `X-PASSWORD`, JSON content negotiation). There is no need for per-request auth logic—resources just rely on the connector.
- `getCompanyId()` exposes the company scope so every request receives the context (all endpoints live under `/v1/company/{companyId}/...`).
- The top-level `Scrada` class owns a single connector instance and exposes fluent accessors (`dailyReceipts()`, `salesInvoices()`, `peppol()`, `inboundDocuments()`), ensuring consumers never instantiate resources manually.

### Resources & Requests
- Each resource extends `Saloon\Http\BaseResource`, is marked `final`, and uses `HandlesResponseErrors` to normalize error handling.
- Methods stay thin: instantiate the correct request object, pass the company ID plus method arguments, send the request, call `throwIfError()`, and transform the payload into DTOs or scalars.
- Any method accepting payloads supports both arrays and DTOs. Helper methods (e.g. `normalizeInvoicePayload`, `normalizePayload`) convert nested DTOs such as `InvoiceLine` or `DailyReceiptLine` into request-ready arrays.
- Requests live under `src/Requests/<Domain>` and are named after the API capability (`CreateSalesInvoiceRequest`, `GetDocumentPdfRequest`, ...). Each request declares the HTTP method, endpoint string, and, when needed, JSON body support via `HasJsonBody`.
- Keep endpoints consistent: `/v1/company/{companyId}` is always prefixed in the request class so resource methods never build URLs manually.

## Domain Resources

| Resource | Responsibilities |
| --- | --- |
| `DailyReceiptsResource` | Fetch payment methods for a journal (`getPaymentMethods`) and add receipt lines (`addLines`). |
| `SalesInvoiceResource` | Create invoices (arrays or DTO payloads), fetch send status, and download UBL XML. |
| `PeppolResource` | Look up Peppol party information for a customer payload. |
| `InboundDocumentResource` | List unconfirmed documents, fetch a single document, download its PDF, and confirm it. |

Each resource translates responses into DTOs (`PaymentMethod`, `DailyReceiptLine`, `SendStatus`, `InboundDocument`, etc.) so SDK users never touch associative arrays.

## Data Transfer Objects
- DTOs live inside `src/Dto` and are `final readonly`. Constructors expose typed promoted properties for every field exposed by the API.
- Every DTO provides a `fromArray(array $data): self` factory. Use `is_string`/`is_numeric` checks and sensible defaults to avoid undefined-index errors.
- DTOs that can be used as request builders (`AddDailyReceiptLinesData`, `DailyReceiptLine`, `CreateSalesInvoiceData`, `SalesInvoice`, `Customer`, `InvoiceLine`, ...) implement `toArray()` so resources can seamlessly convert them into JSON payloads.
- Collections are represented as native arrays with docblocks describing the shape (`@return array<int, PaymentMethod>`). Resources normalize mixed data before mapping to DTOs.

## Error Handling
- `ScradaException` is the base runtime exception and exposes a `fromResponse()` helper to map unknown API errors.
- `AuthenticationException::invalidCredentials()` is thrown when Scrada returns HTTP 401.
- `NotFoundException::resource()` creates readable 404 errors (used when resources pass closures to `HandlesResponseErrors`).
- `ValidationException::fromResponse()` inspects the `errors` payload and exposes an `errors()` accessor for per-field messages.
- The `HandlesResponseErrors` trait centralizes this behaviour: `401 → AuthenticationException`, `404 → NotFoundException`, `422 → ValidationException`, any other failed response → `ScradaException`.

## Testing Strategy
- **Unit tests (`tests/Unit`)** validate DTO hydration/serialization, connector guard clauses, and helper logic. Use PHPUnit expectations inside Pest tests for readability.
- **Feature tests (`tests/Feature`)** instantiate the real resources but rely on `MockClient::global()` or per-connector mocks to simulate HTTP responses. They verify that requests target the correct endpoint/method and that responses are translated into DTOs.
- **Architecture tests (`tests/ArchTest.php`)** ensure the conventions stay intact: all classes are `final`, DTOs are `readonly`, requests extend `Saloon\Http\Request`, resources extend `Saloon\Http\BaseResource`, exceptions extend `ScradaException`, and `dd`/`dump`/`ray` are forbidden.
- Group tests via `tests/Pest.php` so `composer test --group=feature` or similar commands remain possible.

## Tooling, QA & CI Expectations
- Run `composer format`, `composer analyse`, and `composer test` locally before sending a PR. Fix root causes over expanding the PHPStan baseline whenever possible.
- PHPStan uses `build/phpstan` as the temporary directory; keep it cached for faster CI runs.
- `MockClient`-based tests must stub every request that a resource will send—missing mappings will cause Pest to throw, which keeps tests deterministic.
- Keep commits free from formatting-only changes unless the change set intentionally touches formatting (Pint) for the file being edited.

## Adding or Changing Endpoints
1. **Shape the data:** Create or update DTOs in `src/Dto`. Add `fromArray`/`toArray` helpers and unit tests proving conversion logic.
2. **Create the request:** Add a new Saloon request under `src/Requests/<Domain>`. Set the HTTP method, endpoint path (including company ID), query/body payloads, and JSON body trait when needed.
3. **Expose through a resource:** Either add a method to an existing resource (if it matches the domain) or introduce a new `*Resource` class under `src/Resources`. Reuse `HandlesResponseErrors`.
4. **Wire up the entry point:** If you created a new resource, add an accessor in `Scrada` that lazily instantiates it.
5. **Tests:** Write/extend feature tests using `MockClient` plus any DTO/unit coverage that deserializes the new payload. Architecture tests should pass without updates.
6. **Docs:** Update `README.md`, this document, and any samples to describe the new capability when appropriate.
7. **Quality gates:** Finish by running `composer format`, `composer analyse`, and `composer test`.

## Naming & Coding Conventions
- Every class/trait is `final` (the Arch test enforces this) except for `ScradaException`, which serves as the inheritance root for other exceptions.
- Avoid `else` statements. Prefer early returns and guard clauses—this is enforced in tests.
- Requests are suffixed with `Request`, live in `src/Requests/<Domain>`, extend `Saloon\Http\Request`, and use typed constructors for all arguments.
- Resources are suffixed with `Resource`, extend `Saloon\Http\BaseResource`, and never expose raw Saloon responses to consumers.
- DTOs reside in `src/Dto`, remain `final readonly`, and use descriptive property names instead of abbreviations.
- Exceptions extend `ScradaException` and expose named constructors (`resource()`, `invalidCredentials()`, etc.) to keep error messages consistent.
- No debugging helpers (`dd`, `dump`, `ray`, …) are allowed inside `src/`—the architecture test guards this.

## References & Further Reading
- `README.md` – usage examples and installation instructions.
- `CHANGELOG.md` – release history.
- `documentation/SDK_IMPLEMENTATION_PROMPT.md` – original implementation brief.
- `documentation/Scrada Full.postman_collection.json` – annotated Postman collection for API endpoints.
