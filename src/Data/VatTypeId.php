<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data;

use InvalidArgumentException;

/**
 * VAT Type ID mapping for Scrada API.
 *
 * Maps country code and VAT percentage to the correct UUID for the Scrada API.
 * Each country has specific UUIDs for different VAT rates.
 *
 * @see https://docs.scrada.be - Scrada API documentation
 */
final readonly class VatTypeId
{
    /**
     * VAT Type ID mappings by country and percentage.
     *
     * Format: [countryCode => [vatPercentage => uuid]]
     *
     * @var array<string, array<int|string, string>>
     */
    private const MAPPINGS = [
        'BE' => [
            0 => 'cbff0b5e-96e3-4201-91d0-51304cee2605',   // 00 (0%) VAT
            6 => '7befe0fc-7131-4b15-9fe6-ca4b9280b63c',   // 01 (6%) VAT
            12 => '647ed17b-fb6f-4772-baf5-928de98f4db1',  // 02 (12%) VAT
            21 => '8424d909-78b9-483c-9b1d-4584fb537846',  // 03 (21%) VAT
            'NA' => 'cc9b638f-3b54-44c8-91e5-83d337ae6591', // NA (not applicable)
        ],
        'NL' => [
            0 => 'fb145f3f-c866-4322-9169-8d8219d40e8a',   // 00 (0%) VAT
            9 => '3aa951e7-f307-4902-b315-b764eb81d211',   // 02 (9%) VAT
            21 => 'aeb8b26c-00b1-4c6a-9cf2-bc1c71d89196',  // 03 (21%) VAT
            'NA' => 'a29f353e-5549-460a-97cb-70a607b28581', // NA (not applicable)
        ],
        'LU' => [
            0 => '1693aca4-a715-4543-be5d-64f0210f0078',   // 00 (0%) VAT
            3 => '03120fb9-18ea-43a1-9119-1af511895e28',   // 01 (3%) VAT
            8 => '3f3556ee-6afb-43d1-9545-d309087ac461',   // 02 (8%) VAT
            14 => 'd5b9db32-49d7-4929-9e60-dec7b00c2e2f',  // 03 (14%) VAT
            17 => 'e701b521-2ff0-4176-9533-3e297d52809e',  // 04 (17%) VAT
            'NA' => 'ea73a071-18dd-4964-8fd7-fe58ff782c2c', // NA (not applicable)
        ],
    ];

    public function __construct(
        public string $uuid,
        public string $countryCode,
        public float $vatPercentage,
    ) {}

    /**
     * Create a VatTypeId from country code and VAT percentage.
     *
     * @param  string  $countryCode  ISO 3166-1 alpha-2 country code (BE, NL, LU)
     * @param  float  $vatPercentage  The VAT percentage (0, 6, 9, 12, 21, etc.)
     *
     * @throws InvalidArgumentException If country or VAT rate is not supported
     */
    public static function fromCountryAndPercentage(string $countryCode, float $vatPercentage): self
    {
        $countryCode = strtoupper($countryCode);
        $roundedPercentage = (int) round($vatPercentage);

        if (! isset(self::MAPPINGS[$countryCode])) {
            throw new InvalidArgumentException(
                "Country '{$countryCode}' is not supported for VAT type mapping. Supported countries: BE, NL, LU"
            );
        }

        $countryMappings = self::MAPPINGS[$countryCode];

        // Try exact match first
        if (isset($countryMappings[$roundedPercentage])) {
            return new self(
                uuid: $countryMappings[$roundedPercentage],
                countryCode: $countryCode,
                vatPercentage: $vatPercentage,
            );
        }

        // For unsupported percentages, try to find the closest match or use NA
        // This handles edge cases like 0% cross-border transactions
        if ($roundedPercentage === 0 && isset($countryMappings[0])) {
            return new self(
                uuid: $countryMappings[0],
                countryCode: $countryCode,
                vatPercentage: $vatPercentage,
            );
        }

        // If NA is available, use it for unsupported rates
        if (isset($countryMappings['NA'])) {
            return new self(
                uuid: $countryMappings['NA'],
                countryCode: $countryCode,
                vatPercentage: $vatPercentage,
            );
        }

        throw new InvalidArgumentException(
            "VAT percentage {$vatPercentage}% is not supported for country '{$countryCode}'. ".
            'Supported rates: '.implode(', ', array_keys($countryMappings))
        );
    }

    /**
     * Create a VatTypeId for "Not Applicable" (cross-border B2B, exempt, etc.).
     */
    public static function notApplicable(string $countryCode): self
    {
        $countryCode = strtoupper($countryCode);

        if (! isset(self::MAPPINGS[$countryCode]['NA'])) {
            throw new InvalidArgumentException(
                "Country '{$countryCode}' does not have a 'NA' VAT type mapping."
            );
        }

        return new self(
            uuid: self::MAPPINGS[$countryCode]['NA'],
            countryCode: $countryCode,
            vatPercentage: 0,
        );
    }

    /**
     * Check if a country is supported.
     */
    public static function isCountrySupported(string $countryCode): bool
    {
        return isset(self::MAPPINGS[strtoupper($countryCode)]);
    }

    /**
     * Get all supported country codes.
     *
     * @return array<string>
     */
    public static function supportedCountries(): array
    {
        return array_keys(self::MAPPINGS);
    }

    /**
     * Get all supported VAT rates for a country.
     *
     * @return array<int|string>
     */
    public static function supportedRatesForCountry(string $countryCode): array
    {
        $countryCode = strtoupper($countryCode);

        if (! isset(self::MAPPINGS[$countryCode])) {
            return [];
        }

        return array_keys(self::MAPPINGS[$countryCode]);
    }

    /**
     * Get the UUID value for API requests.
     */
    public function getValue(): string
    {
        return $this->uuid;
    }

    public function __toString(): string
    {
        return $this->uuid;
    }
}
