<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Dto;

/**
 * Request data for adding daily receipt lines.
 */
final readonly class AddDailyReceiptLinesData
{
    /**
     * @param array<int, DailyReceiptLine> $lines
     * @param array<int, array{paymentMethodID: string, amount: float}> $paymentMethods
     */
    public function __construct(
        public string $date,
        public array $lines,
        public array $paymentMethods,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $lines = array_map(
            static fn (array $line): DailyReceiptLine => DailyReceiptLine::fromArray($line),
            array_values(array_filter(
                array: is_array($data['lines'] ?? null) ? $data['lines'] : [],
                callback: static fn (mixed $line): bool => is_array($line)
            ))
        );

        /** @var array<int, array{paymentMethodID: string, amount: float}> $paymentMethods */
        $paymentMethods = array_values(array_filter(
            array: is_array($data['paymentMethods'] ?? null) ? $data['paymentMethods'] : [],
            callback: static fn (mixed $method): bool => is_array($method)
        ));

        return new self(
            date: is_string($data['date'] ?? null) ? $data['date'] : '',
            lines: $lines,
            paymentMethods: $paymentMethods,
        );
    }

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'lines' => array_map(
                static fn (DailyReceiptLine $line): array => $line->toArray(),
                $this->lines
            ),
            'paymentMethods' => $this->paymentMethods,
        ];
    }
}
