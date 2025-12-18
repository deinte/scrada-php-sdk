<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Enums;

/**
 * File types for attachments.
 */
enum FileType: int
{
    case PDF = 1;
    case IMAGE = 2;
    case XML = 3;

    public function label(): string
    {
        return match ($this) {
            self::PDF => 'PDF',
            self::IMAGE => 'Image',
            self::XML => 'XML',
        };
    }

    /**
     * @return array<int, string>
     */
    public function mimeTypes(): array
    {
        return match ($this) {
            self::PDF => ['application/pdf'],
            self::IMAGE => ['image/png', 'image/jpeg', 'image/gif', 'image/webp'],
            self::XML => ['application/xml', 'text/xml'],
        };
    }

    public static function fromMimeType(string $mimeType): self
    {
        return match (true) {
            str_contains($mimeType, 'pdf') => self::PDF,
            str_contains($mimeType, 'image') => self::IMAGE,
            str_contains($mimeType, 'xml') => self::XML,
            default => self::PDF,
        };
    }
}
