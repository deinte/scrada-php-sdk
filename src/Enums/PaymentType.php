<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Enums;

/**
 * Scrada payment type codes.
 *
 * Note: These are Scrada-specific codes, NOT PEPPOL/UBL payment means codes.
 */
enum PaymentType: int
{
    case BankTransfer = 1;
    case Bancontact = 9;

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Overschrijving',
            self::Bancontact => 'Bancontact',
        };
    }
}
