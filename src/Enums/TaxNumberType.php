<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Enums;

/**
 * Tax number types used to identify a party.
 *
 * @see https://www.scrada.be/api-documentation
 */
enum TaxNumberType: int
{
    /**
     * Numero d'entreprise / ondernemingsnummer / Unternehmensnummer / Enterprise number (Belgium)
     */
    case ENTERPRISE_NUMBER_BE = 1;

    /**
     * Kamer van koophandel nummer (the Netherlands)
     */
    case KVK_NL = 2;

    /**
     * SIRENE (France)
     */
    case SIRENE_FR = 3;

    public function label(): string
    {
        return match ($this) {
            self::ENTERPRISE_NUMBER_BE => 'Enterprise number (Belgium)',
            self::KVK_NL => 'KvK number (Netherlands)',
            self::SIRENE_FR => 'SIRENE (France)',
        };
    }

    public function countryCode(): string
    {
        return match ($this) {
            self::ENTERPRISE_NUMBER_BE => 'BE',
            self::KVK_NL => 'NL',
            self::SIRENE_FR => 'FR',
        };
    }
}
