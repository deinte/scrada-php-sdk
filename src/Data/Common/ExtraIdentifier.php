<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Data\Common;

/**
 * Extra identifier information for a party.
 */
final readonly class ExtraIdentifier
{
    public function __construct(
        public ?string $scheme = null,
        public ?string $value = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            scheme: isset($data['scheme']) && is_string($data['scheme']) ? $data['scheme'] : null,
            value: isset($data['value']) && is_string($data['value']) ? $data['value'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'scheme' => $this->scheme,
            'value' => $this->value,
        ], static fn (mixed $v): bool => $v !== null);
    }
}
