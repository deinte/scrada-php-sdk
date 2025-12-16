<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Data\AddDailyReceiptLinesData;
use Deinte\ScradaSdk\Data\DailyReceiptLine;

it('creates request data from array', function (): void {
    $data = [
        'date' => '2021-07-30',
        'lines' => [
            [
                'lineType' => 1,
                'vatTypeID' => 'vat-uuid',
                'vatPerc' => 21.0,
                'amount' => 500.0,
                'categoryID' => 'category-uuid',
            ],
        ],
        'paymentMethods' => [
            [
                'paymentMethodID' => 'payment-uuid',
                'amount' => 200.0,
            ],
        ],
    ];

    $requestData = AddDailyReceiptLinesData::fromArray($data);

    expect($requestData->date)->toBe('2021-07-30')
        ->and($requestData->lines)->toHaveCount(1)
        ->and($requestData->lines[0])->toBeInstanceOf(DailyReceiptLine::class)
        ->and($requestData->paymentMethods)->toHaveCount(1);
});

it('converts request data to array payload', function (): void {
    $requestData = new AddDailyReceiptLinesData(
        date: '2021-07-30',
        lines: [
            new DailyReceiptLine(
                lineType: 1,
                vatTypeId: 'vat-uuid',
                vatPercentage: 21.0,
                amount: 500.0,
                categoryId: 'category-uuid'
            ),
        ],
        paymentMethods: [
            [
                'paymentMethodID' => 'payment-uuid',
                'amount' => 200.0,
            ],
        ],
    );

    $payload = $requestData->toArray();

    expect($payload)->toHaveKey('date');
    expect($payload['date'])->toBe('2021-07-30');
    expect($payload['lines'])->toBeArray()->toHaveCount(1);

    $lines = $payload['lines'];
    if (is_array($lines) && isset($lines[0])) {
        expect($lines[0])->toBeArray();
    }

    expect($payload['paymentMethods'])->toHaveCount(1);
});

it('handles empty arrays gracefully', function (): void {
    $requestData = AddDailyReceiptLinesData::fromArray([
        'date' => '2021-07-30',
    ]);

    expect($requestData->date)->toBe('2021-07-30')
        ->and($requestData->lines)->toBeEmpty()
        ->and($requestData->paymentMethods)->toBeEmpty();
});
