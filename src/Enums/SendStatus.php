<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Enums;

/**
 * Status values for outbound documents (Peppol/email).
 *
 * @see https://www.scrada.be/api-documentation
 */
enum SendStatus: string
{
    /**
     * A new document is sent to Scrada to be uploaded to Peppol or send by email.
     */
    case CREATED = 'Created';

    /**
     * When a document is successfully uploaded to Peppol or sent by email.
     */
    case PROCESSED = 'Processed';

    /**
     * When there is an issue uploading to Peppol or sending email.
     * Scrada will retry up to 10 times. Check ErrorMessage for details.
     */
    case RETRY = 'Retry';

    /**
     * When a document was canceled to be uploaded to Peppol or sent by email.
     * This can only be done using the GUI of Scrada.
     */
    case CANCELED = 'Canceled';

    /**
     * There is an error sending to Peppol or by email.
     * Check error message for more details.
     */
    case ERROR = 'Error';

    /**
     * Document was already processed by the other access point.
     * Can happen on retry or when resending via GUI.
     */
    case ERROR_ALREADY_SENT = 'Error already sent';

    /**
     * The receiverID is not registered on Peppol.
     */
    case ERROR_NOT_ON_PEPPOL = 'Error not on Peppol';

    /**
     * ReceiverID is blocked for Peppol, document will be sent by email.
     * (Full subscription only)
     */
    case BLOCKED_SEND_BY_EMAIL = 'Blocked - send by email';

    /**
     * ReceiverID not on Peppol, document will be sent by email.
     * (Full subscription only)
     */
    case NOT_ON_PEPPOL_SEND_BY_EMAIL = 'Not on Peppol - send by email';

    /**
     * Error sending to Peppol, document will be sent by email.
     * (Full subscription only)
     */
    case ERROR_SEND_BY_EMAIL = 'Error - send by email';

    /**
     * ReceiverID is blocked for Peppol and no email provided.
     * (Full subscription only)
     */
    case BLOCKED = 'Blocked';

    /**
     * No configuration in Scrada how to send the sales invoice.
     */
    case NONE = 'None';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Created',
            self::PROCESSED => 'Processed',
            self::RETRY => 'Retry',
            self::CANCELED => 'Canceled',
            self::ERROR => 'Error',
            self::ERROR_ALREADY_SENT => 'Error already sent',
            self::ERROR_NOT_ON_PEPPOL => 'Error not on Peppol',
            self::BLOCKED_SEND_BY_EMAIL => 'Blocked - send by email',
            self::NOT_ON_PEPPOL_SEND_BY_EMAIL => 'Not on Peppol - send by email',
            self::ERROR_SEND_BY_EMAIL => 'Error - send by email',
            self::BLOCKED => 'Blocked',
            self::NONE => 'None',
        };
    }

    public function isSuccess(): bool
    {
        return $this === self::PROCESSED;
    }

    public function isError(): bool
    {
        return in_array($this, [
            self::ERROR,
            self::ERROR_ALREADY_SENT,
            self::ERROR_NOT_ON_PEPPOL,
            self::ERROR_SEND_BY_EMAIL,
        ], true);
    }

    public function isPending(): bool
    {
        return in_array($this, [
            self::CREATED,
            self::RETRY,
        ], true);
    }
}
