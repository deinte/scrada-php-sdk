<?php

declare(strict_types=1);

namespace Deinte\ScradaSdk\Enums;

/**
 * VAT types for Scrada invoices.
 *
 * Maps to Belgian VAT declaration boxes.
 *
 * @see https://docs.scrada.be - Scrada API documentation
 */
enum VatType: int
{
    /**
     * Standard rate (21% in Belgium).
     * If line is 0% VAT then Zero rate must be used.
     * Belgium: VAT Box 01, 02 or 03
     */
    case STANDARD = 1;

    /**
     * Zero rate (0% VAT).
     * Belgium: VAT Box 00
     */
    case ZERO_RATE = 2;

    /**
     * Exempt from tax (Vrijgesteld van BTW / Divers hors TVA).
     * Use for domestic 0% VAT transactions.
     * Belgium: Not on VAT Declaration
     */
    case EXEMPT = 3;

    /**
     * ICD Services B2B (Intracommunautaire diensten).
     * Cross-border EU B2B services with reverse charge.
     * Belgium: VAT Box 44
     */
    case ICD_SERVICES_B2B = 4;

    /**
     * ICD Goods (Intracommunautaire goederen).
     * Cross-border EU B2B goods.
     * Belgium: VAT Box 46
     */
    case ICD_GOODS = 5;

    /**
     * ICD Manufacturing cost.
     * Belgium: VAT Box 47
     */
    case ICD_MANUFACTURING = 6;

    /**
     * ICD Assembly.
     * Belgium: VAT Box 47
     */
    case ICD_ASSEMBLY = 7;

    /**
     * ICD Distance.
     * Belgium: VAT Box 47
     */
    case ICD_DISTANCE = 8;

    /**
     * ICD Services.
     * Belgium: VAT Box 47
     */
    case ICD_SERVICES = 9;

    /**
     * ICD Triangle a-B-c.
     * Belgium: VAT Box 46
     */
    case ICD_TRIANGLE = 10;

    /**
     * Export non E.U.
     * Belgium: VAT Box 47
     */
    case EXPORT_NON_EU = 20;

    /**
     * Indirect export.
     * Belgium: VAT Box 47
     */
    case EXPORT_INDIRECT = 21;

    /**
     * Export via E.U.
     * Belgium: VAT Box 47
     */
    case EXPORT_VIA_EU = 22;

    /**
     * Reverse charge (Medecontractant).
     * Belgium: VAT Box 45
     */
    case REVERSE_CHARGE = 50;

    /**
     * Financial discount.
     * Belgium: Not on VAT Declaration
     */
    case FINANCIAL_DISCOUNT = 51;

    /**
     * 0% Clause 44 (Article 44).
     * Belgium: VAT Box 00
     */
    case ZERO_CLAUSE_44 = 52;

    /**
     * Standard exchange.
     * Belgium: VAT Box 03
     */
    case STANDARD_EXCHANGE = 53;

    /**
     * Margin scheme.
     */
    case MARGIN = 54;

    /**
     * OSS Goods.
     */
    case OSS_GOODS = 70;

    /**
     * OSS Services.
     */
    case OSS_SERVICES = 71;

    /**
     * OSS Import.
     */
    case OSS_IMPORT = 72;

    /**
     * Get VatType from VAT percentage for domestic Belgian invoices.
     */
    public static function fromPercentageDomestic(float $percentage): self
    {
        return match (true) {
            $percentage > 0 => self::STANDARD,
            default => self::EXEMPT, // 0% domestic = exempt, not ICD
        };
    }

    /**
     * Get VatType from VAT percentage for cross-border EU B2B invoices.
     */
    public static function fromPercentageCrossBorderB2B(float $percentage, bool $isService = true): self
    {
        return match (true) {
            $percentage > 0 => self::STANDARD,
            $isService => self::ICD_SERVICES_B2B,
            default => self::ICD_GOODS,
        };
    }

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Standard rate',
            self::ZERO_RATE => 'Zero rate',
            self::EXEMPT => 'Exempt from tax',
            self::ICD_SERVICES_B2B => 'ICD Services B2B',
            self::ICD_GOODS => 'ICD Goods',
            self::ICD_MANUFACTURING => 'ICD Manufacturing',
            self::ICD_ASSEMBLY => 'ICD Assembly',
            self::ICD_DISTANCE => 'ICD Distance',
            self::ICD_SERVICES => 'ICD Services',
            self::ICD_TRIANGLE => 'ICD Triangle',
            self::EXPORT_NON_EU => 'Export non E.U.',
            self::EXPORT_INDIRECT => 'Indirect export',
            self::EXPORT_VIA_EU => 'Export via E.U.',
            self::REVERSE_CHARGE => 'Reverse charge',
            self::FINANCIAL_DISCOUNT => 'Financial discount',
            self::ZERO_CLAUSE_44 => '0% Clause 44',
            self::STANDARD_EXCHANGE => 'Standard exchange',
            self::MARGIN => 'Margin scheme',
            self::OSS_GOODS => 'OSS Goods',
            self::OSS_SERVICES => 'OSS Services',
            self::OSS_IMPORT => 'OSS Import',
        };
    }
}
