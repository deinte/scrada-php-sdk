<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Enums;

/**
 * Methods for sending sales invoices to customers.
 *
 * @see https://www.scrada.be/api-documentation
 */
enum SendMethod: string
{
    /**
     * The sales invoice is sent or trying to send over Peppol.
     */
    case PEPPOL = 'Peppol';

    /**
     * The sales invoice is sent or trying to send over email.
     */
    case EMAIL = 'Email';

    /**
     * The sales invoice is sent by Peppol and a copy is sent by email.
     */
    case PEPPOL_AND_EMAIL = 'Peppol and email';

    /**
     * There was no configuration how to send the invoice to the customer.
     */
    case NONE = 'None';

    public function label(): string
    {
        return match ($this) {
            self::PEPPOL => 'Peppol',
            self::EMAIL => 'Email',
            self::PEPPOL_AND_EMAIL => 'Peppol and email',
            self::NONE => 'None',
        };
    }

    public function usesPeppol(): bool
    {
        return in_array($this, [self::PEPPOL, self::PEPPOL_AND_EMAIL], true);
    }

    public function usesEmail(): bool
    {
        return in_array($this, [self::EMAIL, self::PEPPOL_AND_EMAIL], true);
    }
}
