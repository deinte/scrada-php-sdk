<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Enums;

/**
 * Standard item identification types.
 *
 * Specifies the type of identification number used to uniquely identify
 * a company, organization, or item.
 *
 * @see https://www.scrada.be/api-documentation
 */
enum ItemIdentificationType: int
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

    /**
     * Global Location Number [GLN] (must be 13 digits)
     */
    case GLN = 20;

    /**
     * Global Trade Item Number [GTIN] (must be 8, 12, 13, or 14 digits)
     */
    case GTIN = 21;

    /**
     * GS1 identification key (must be between 8 and 20 digits)
     */
    case GS1 = 22;

    public function label(): string
    {
        return match ($this) {
            self::ENTERPRISE_NUMBER_BE => 'Enterprise number (Belgium)',
            self::KVK_NL => 'KvK number (Netherlands)',
            self::SIRENE_FR => 'SIRENE (France)',
            self::GLN => 'Global Location Number (GLN)',
            self::GTIN => 'Global Trade Item Number (GTIN)',
            self::GS1 => 'GS1 identification key',
        };
    }
}
