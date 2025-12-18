<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Enums;

/**
 * Unit of measure types for invoice lines.
 *
 * @see https://www.scrada.be/api-documentation
 */
enum UnitType: int
{
    case UNIT = 1;
    case PIECE = 2;
    case PALLET = 3;
    case CONTAINER_20FT = 4;
    case CONTAINER_40FT = 5;

    // Time units
    case SECOND = 100;
    case MINUTE = 101;
    case HOUR = 102;
    case DAY = 103;
    case MONTH = 104;
    case YEAR = 105;
    case WEEK = 106;

    // Weight units
    case MILLIGRAM = 200;
    case GRAM = 201;
    case KILOGRAM = 202;
    case TON = 203;

    // Length units
    case METER = 300;
    case KILOMETER = 301;

    // Volume units
    case LITER = 400;
    case MILLILITER = 401;

    public function label(): string
    {
        return match ($this) {
            self::UNIT => 'Unit',
            self::PIECE => 'Piece',
            self::PALLET => 'Pallet',
            self::CONTAINER_20FT => 'Container 20ft',
            self::CONTAINER_40FT => 'Container 40ft',
            self::SECOND => 'Second',
            self::MINUTE => 'Minute',
            self::HOUR => 'Hour',
            self::DAY => 'Day',
            self::MONTH => 'Month',
            self::YEAR => 'Year',
            self::WEEK => 'Week',
            self::MILLIGRAM => 'Milligram',
            self::GRAM => 'Gram',
            self::KILOGRAM => 'Kilogram',
            self::TON => 'Ton',
            self::METER => 'Meter',
            self::KILOMETER => 'Kilometer',
            self::LITER => 'Liter',
            self::MILLILITER => 'Milliliter',
        };
    }

    public function abbreviation(): string
    {
        return match ($this) {
            self::UNIT => 'ea',
            self::PIECE => 'pc',
            self::PALLET => 'plt',
            self::CONTAINER_20FT => '20ft',
            self::CONTAINER_40FT => '40ft',
            self::SECOND => 's',
            self::MINUTE => 'min',
            self::HOUR => 'h',
            self::DAY => 'd',
            self::MONTH => 'mo',
            self::YEAR => 'y',
            self::WEEK => 'wk',
            self::MILLIGRAM => 'mg',
            self::GRAM => 'g',
            self::KILOGRAM => 'kg',
            self::TON => 't',
            self::METER => 'm',
            self::KILOMETER => 'km',
            self::LITER => 'L',
            self::MILLILITER => 'mL',
        };
    }
}
