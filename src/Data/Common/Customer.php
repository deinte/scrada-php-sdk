<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\Common;

use Deinte\ScradaSdk\Enums\TaxNumberType;

/**
 * Represents a Scrada customer/party entity.
 *
 * Used for Peppol party lookup and invoice customer data.
 */
final readonly class Customer
{
    /**
     * @param  array<int, ExtraIdentifier>  $extraIdentifiers
     */
    public function __construct(
        public string $name,
        public Address $address,
        public ?string $peppolID = null,
        public ?string $code = null,
        public ?string $accountingCode = null,
        public ?string $languageCode = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $invoiceEmail = null,
        public ?string $contact = null,
        public ?TaxNumberType $taxNumberType = null,
        public ?string $taxNumber = null,
        public ?string $vatNumber = null,
        public ?string $glnNumber = null,
        public array $extraIdentifiers = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $name = $data['name'] ?? $data['customerName'] ?? '';
        $address = $data['address'] ?? [];

        $taxNumberTypeValue = $data['taxNumberType'] ?? null;
        $taxNumberType = match (true) {
            $taxNumberTypeValue instanceof TaxNumberType => $taxNumberTypeValue,
            is_int($taxNumberTypeValue) => TaxNumberType::tryFrom($taxNumberTypeValue),
            is_numeric($taxNumberTypeValue) => TaxNumberType::tryFrom((int) $taxNumberTypeValue),
            default => null,
        };

        $extraIdentifiers = array_map(
            static fn (array $identifier): ExtraIdentifier => ExtraIdentifier::fromArray($identifier),
            array_values(array_filter(
                is_array($data['extraIdentifiers'] ?? null) ? $data['extraIdentifiers'] : [],
                static fn (mixed $item): bool => is_array($item),
            )),
        );

        return new self(
            name: is_string($name) ? $name : '',
            address: Address::fromArray(is_array($address) ? $address : []),
            peppolID: self::nullableString($data['peppolID'] ?? null),
            code: self::nullableString($data['code'] ?? null),
            accountingCode: self::nullableString($data['accountingCode'] ?? null),
            languageCode: self::nullableString($data['languageCode'] ?? null),
            phone: self::nullableString($data['phone'] ?? null),
            email: self::nullableString($data['email'] ?? null),
            invoiceEmail: self::nullableString($data['invoiceEmail'] ?? null),
            contact: self::nullableString($data['contact'] ?? null),
            taxNumberType: $taxNumberType,
            taxNumber: self::nullableString($data['taxNumber'] ?? null),
            vatNumber: self::nullableString($data['vatNumber'] ?? null),
            glnNumber: self::nullableString($data['glnNumber'] ?? null),
            extraIdentifiers: $extraIdentifiers,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'name' => $this->name,
            'address' => $this->address->toArray(),
        ];

        if ($this->peppolID !== null) {
            $payload['peppolID'] = $this->peppolID;
        }

        if ($this->code !== null) {
            $payload['code'] = $this->code;
        }

        if ($this->accountingCode !== null) {
            $payload['accountingCode'] = $this->accountingCode;
        }

        if ($this->languageCode !== null) {
            $payload['languageCode'] = $this->languageCode;
        }

        if ($this->phone !== null) {
            $payload['phone'] = $this->phone;
        }

        if ($this->email !== null) {
            $payload['email'] = $this->email;
        }

        if ($this->invoiceEmail !== null) {
            $payload['invoiceEmail'] = $this->invoiceEmail;
        }

        if ($this->contact !== null) {
            $payload['contact'] = $this->contact;
        }

        if ($this->taxNumberType !== null) {
            $payload['taxNumberType'] = $this->taxNumberType->value;
        }

        if ($this->taxNumber !== null) {
            $payload['taxNumber'] = $this->taxNumber;
        }

        if ($this->vatNumber !== null) {
            $payload['vatNumber'] = $this->vatNumber;
        }

        if ($this->glnNumber !== null) {
            $payload['glnNumber'] = $this->glnNumber;
        }

        if ($this->extraIdentifiers !== []) {
            $payload['extraIdentifiers'] = array_map(
                static fn (ExtraIdentifier $identifier): array => $identifier->toArray(),
                $this->extraIdentifiers,
            );
        }

        return $payload;
    }

    private static function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
