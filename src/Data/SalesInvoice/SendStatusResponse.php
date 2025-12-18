<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\SalesInvoice;

use Deinte\ScradaSdk\Enums\SendMethod;
use Deinte\ScradaSdk\Enums\SendStatus;

/**
 * Response from getting the send status of a sales invoice.
 */
final readonly class SendStatusResponse
{
    public function __construct(
        public ?string $id,
        public ?SendStatus $status,
        public ?SendMethod $sendMethod,
        public ?string $createdOn,
        public ?string $externalReference,
        public ?string $peppolSenderID,
        public ?string $peppolReceiverID,
        public ?string $peppolC1CountryCode,
        public ?string $peppolC2Timestamp,
        public ?string $peppolC2SeatID,
        public ?string $peppolC2MessageID,
        public ?string $peppolC3MessageID,
        public ?string $peppolC3Timestamp,
        public ?string $peppolC3SeatID,
        public ?string $peppolConversationID,
        public ?string $peppolSbdhInstanceID,
        public ?string $peppolDocumentTypeScheme,
        public ?string $peppolDocumentTypeValue,
        public ?string $peppolProcessScheme,
        public ?string $peppolProcessValue,
        public ?int $attempt,
        public ?string $errorMessage,
        public ?string $peppolOutboundDocumentID,
        public ?string $receiverEmailAddress,
        public ?string $receiverEmailTime,
        public ?string $receiverEmailStatus,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $statusValue = $data['status'] ?? null;
        $status = is_string($statusValue) ? SendStatus::tryFrom($statusValue) : null;

        $sendMethodValue = $data['sendMethod'] ?? null;
        $sendMethod = is_string($sendMethodValue) ? SendMethod::tryFrom($sendMethodValue) : null;

        return new self(
            id: self::nullableString($data['id'] ?? null),
            status: $status,
            sendMethod: $sendMethod,
            createdOn: self::nullableString($data['createdOn'] ?? null),
            externalReference: self::nullableString($data['externalReference'] ?? null),
            peppolSenderID: self::nullableString($data['peppolSenderID'] ?? null),
            peppolReceiverID: self::nullableString($data['peppolReceiverID'] ?? null),
            peppolC1CountryCode: self::nullableString($data['peppolC1CountryCode'] ?? null),
            peppolC2Timestamp: self::nullableString($data['peppolC2Timestamp'] ?? null),
            peppolC2SeatID: self::nullableString($data['peppolC2SeatID'] ?? null),
            peppolC2MessageID: self::nullableString($data['peppolC2MessageID'] ?? null),
            peppolC3MessageID: self::nullableString($data['peppolC3MessageID'] ?? null),
            peppolC3Timestamp: self::nullableString($data['peppolC3Timestamp'] ?? null),
            peppolC3SeatID: self::nullableString($data['peppolC3SeatID'] ?? null),
            peppolConversationID: self::nullableString($data['peppolConversationID'] ?? null),
            peppolSbdhInstanceID: self::nullableString($data['peppolSbdhInstanceID'] ?? null),
            peppolDocumentTypeScheme: self::nullableString($data['peppolDocumentTypeScheme'] ?? null),
            peppolDocumentTypeValue: self::nullableString($data['peppolDocumentTypeValue'] ?? null),
            peppolProcessScheme: self::nullableString($data['peppolProcessScheme'] ?? null),
            peppolProcessValue: self::nullableString($data['peppolProcessValue'] ?? null),
            attempt: isset($data['attempt']) && is_numeric($data['attempt']) ? (int) $data['attempt'] : null,
            errorMessage: self::nullableString($data['errorMessage'] ?? null),
            peppolOutboundDocumentID: self::nullableString($data['peppolOutboundDocumentID'] ?? null),
            receiverEmailAddress: self::nullableString($data['receiverEmailAddress'] ?? null),
            receiverEmailTime: self::nullableString($data['receiverEmailTime'] ?? null),
            receiverEmailStatus: self::nullableString($data['receiverEmailStatus'] ?? null),
        );
    }

    /**
     * Check if the document was successfully sent.
     */
    public function isSuccess(): bool
    {
        return $this->status?->isSuccess() ?? false;
    }

    /**
     * Check if there was an error sending the document.
     */
    public function isError(): bool
    {
        return $this->status?->isError() ?? false;
    }

    /**
     * Check if the document is still pending/being processed.
     */
    public function isPending(): bool
    {
        return $this->status?->isPending() ?? false;
    }

    /**
     * Check if the document was sent via Peppol.
     */
    public function wasSentViaPeppol(): bool
    {
        return $this->sendMethod?->usesPeppol() ?? false;
    }

    /**
     * Check if the document was sent via email.
     */
    public function wasSentViaEmail(): bool
    {
        return $this->sendMethod?->usesEmail() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'status' => $this->status?->value,
            'sendMethod' => $this->sendMethod?->value,
            'createdOn' => $this->createdOn,
            'externalReference' => $this->externalReference,
            'peppolSenderID' => $this->peppolSenderID,
            'peppolReceiverID' => $this->peppolReceiverID,
            'peppolC1CountryCode' => $this->peppolC1CountryCode,
            'peppolC2Timestamp' => $this->peppolC2Timestamp,
            'peppolC2SeatID' => $this->peppolC2SeatID,
            'peppolC2MessageID' => $this->peppolC2MessageID,
            'peppolC3MessageID' => $this->peppolC3MessageID,
            'peppolC3Timestamp' => $this->peppolC3Timestamp,
            'peppolC3SeatID' => $this->peppolC3SeatID,
            'peppolConversationID' => $this->peppolConversationID,
            'peppolSbdhInstanceID' => $this->peppolSbdhInstanceID,
            'peppolDocumentTypeScheme' => $this->peppolDocumentTypeScheme,
            'peppolDocumentTypeValue' => $this->peppolDocumentTypeValue,
            'peppolProcessScheme' => $this->peppolProcessScheme,
            'peppolProcessValue' => $this->peppolProcessValue,
            'attempt' => $this->attempt,
            'errorMessage' => $this->errorMessage,
            'peppolOutboundDocumentID' => $this->peppolOutboundDocumentID,
            'receiverEmailAddress' => $this->receiverEmailAddress,
            'receiverEmailTime' => $this->receiverEmailTime,
            'receiverEmailStatus' => $this->receiverEmailStatus,
        ], static fn (mixed $value): bool => $value !== null);
    }

    private static function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
