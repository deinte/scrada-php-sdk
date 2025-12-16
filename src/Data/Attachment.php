<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data;

/**
 * Represents an invoice attachment.
 *
 * Field mappings match the Scrada API:
 * - filename: Name of the file
 * - fileType: Integer file type (1=PDF, 2=Image, etc.)
 * - mimeType: MIME type of the file
 * - base64Data: Base64 encoded file content
 * - note: Optional note about the attachment
 * - externalReference: Optional external reference
 */
final readonly class Attachment
{
    public function __construct(
        public string $filename,
        public string $base64Data,
        public int $fileType = 1,
        public string $mimeType = 'application/pdf',
        public ?string $note = null,
        public ?string $externalReference = null,
    ) {
    }

    /**
     * Create an attachment from a file path.
     */
    public static function fromFile(string $path, ?string $filename = null, ?string $mimeType = null): self
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw new \InvalidArgumentException("Could not read file: {$path}");
        }

        return new self(
            filename: $filename ?? basename($path),
            base64Data: base64_encode($content),
            fileType: self::detectFileType($mimeType ?? mime_content_type($path) ?: 'application/pdf'),
            mimeType: $mimeType ?? mime_content_type($path) ?: 'application/pdf',
        );
    }

    /**
     * Create a PDF attachment from base64 data.
     */
    public static function pdf(string $filename, string $base64Data): self
    {
        return new self(
            filename: $filename,
            base64Data: $base64Data,
            fileType: 1,
            mimeType: 'application/pdf',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            filename: is_string($data['filename'] ?? null) ? $data['filename'] : '',
            base64Data: is_string($data['base64Data'] ?? null) ? $data['base64Data'] : '',
            fileType: is_int($data['fileType'] ?? null) ? $data['fileType'] : 1,
            mimeType: is_string($data['mimeType'] ?? null) ? $data['mimeType'] : 'application/pdf',
            note: is_string($data['note'] ?? null) ? $data['note'] : null,
            externalReference: is_string($data['externalReference'] ?? null) ? $data['externalReference'] : null,
        );
    }

    /**
     * Detect file type integer from MIME type.
     */
    private static function detectFileType(string $mimeType): int
    {
        return match (true) {
            str_contains($mimeType, 'pdf') => 1,
            str_contains($mimeType, 'image') => 2,
            str_contains($mimeType, 'xml') => 3,
            default => 1,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'filename' => $this->filename,
            'fileType' => $this->fileType,
            'mimeType' => $this->mimeType,
            'base64Data' => $this->base64Data,
        ];

        if ($this->note !== null) {
            $payload['note'] = $this->note;
        }

        if ($this->externalReference !== null) {
            $payload['externalReference'] = $this->externalReference;
        }

        return $payload;
    }
}
