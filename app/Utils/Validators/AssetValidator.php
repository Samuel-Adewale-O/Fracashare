<?php

namespace App\Utils\Validators;

class AssetValidator
{
    public static function validateSharePrice(float $sharePrice, float $totalValue, int $totalShares): bool
    {
        $calculatedPrice = $totalValue / $totalShares;
        return abs($sharePrice - $calculatedPrice) < 0.01; // Allow for minor floating point differences
    }

    public static function validateMinimumInvestment(float $minimumInvestment, float $sharePrice): bool
    {
        return $minimumInvestment >= $sharePrice;
    }

    public static function validateRiskLevel(string $riskLevel): bool
    {
        return in_array($riskLevel, ['low', 'medium', 'high']);
    }
}