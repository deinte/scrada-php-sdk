<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\Common;

use Deinte\ScradaSdk\Enums\FileType;

/**
 * Represents an invoice attachment.
 */
final readonly class Attachment
{
    public function __construct(
        public string $filename,
        public string $base64Data,
        public FileType $fileType = FileType::PDF,
        public string $mimeType = 'application/pdf',
        public ?string $note = null,
        public ?string $externalReference = null,
    ) {
    }

    /**
     * Create an attachment from a file path.
     *
     * @throws \InvalidArgumentException If file does not exist or cannot be read
     */
    public static function fromFile(string $path, ?string $filename = null, ?string $mimeType = null): self
    {
        if (! file_exists($path) || ! is_file($path)) {
            throw new \InvalidArgumentException("File not found: {$path}");
        }

        $realPath = realpath($path);

        if ($realPath === false) {
            throw new \InvalidArgumentException("Invalid file path: {$path}");
        }

        $content = file_get_contents($realPath);

        if ($content === false) {
            throw new \InvalidArgumentException("Could not read file: {$path}");
        }

        $detectedMimeType = $mimeType ?? mime_content_type($realPath) ?: 'application/pdf';

        return new self(
            filename: $filename ?? basename($realPath),
            base64Data: base64_encode($content),
            fileType: FileType::fromMimeType($detectedMimeType),
            mimeType: $detectedMimeType,
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
            fileType: FileType::PDF,
            mimeType: 'application/pdf',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $fileTypeValue = $data['fileType'] ?? 1;
        $fileType = is_int($fileTypeValue)
            ? (FileType::tryFrom($fileTypeValue) ?? FileType::PDF)
            : FileType::PDF;

        return new self(
            filename: is_string($data['filename'] ?? null) ? $data['filename'] : '',
            base64Data: is_string($data['base64Data'] ?? null) ? $data['base64Data'] : '',
            fileType: $fileType,
            mimeType: is_string($data['mimeType'] ?? null) ? $data['mimeType'] : 'application/pdf',
            note: isset($data['note']) && is_string($data['note']) ? $data['note'] : null,
            externalReference: isset($data['externalReference']) && is_string($data['externalReference']) ? $data['externalReference'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'filename' => $this->filename,
            'fileType' => $this->fileType->value,
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
