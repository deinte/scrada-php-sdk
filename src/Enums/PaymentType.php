<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Enums;

/**
 * Scrada payment type codes (v1.CompanyInvoicePaymentMethodType).
 *
 * Note: These are Scrada-specific codes, NOT PEPPOL/UBL payment means codes.
 * Scrada only puts the iban/bic on the Peppol UBL PayeeFinancialAccount for
 * a wire transfer, so a bank account needs BankTransfer, never Unknown.
 */
enum PaymentType: int
{
    case Unknown = 1;
    case BankTransfer = 2;
    case BankCard = 3;
    case DirectDebit = 4;
    case Cash = 5;
    case CreditCard = 6;
    case OnlinePaymentProvider = 7;
    case Cheque = 8;
    case Bancontact = 9;
    case StandingAgreement = 10;

    public function label(): string
    {
        return match ($this) {
            self::Unknown => 'Onbekend',
            self::BankTransfer => 'Overschrijving',
            self::BankCard => 'Bankkaart',
            self::DirectDebit => 'Domiciliëring',
            self::Cash => 'Cash',
            self::CreditCard => 'Kredietkaart',
            self::OnlinePaymentProvider => 'Online betaling',
            self::Cheque => 'Cheque',
            self::Bancontact => 'Bancontact',
            self::StandingAgreement => 'Doorlopende overeenkomst',
        };
    }
}
