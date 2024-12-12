<?php

namespace App\Utils;

class NumberFormatter
{
    public static function formatMoney(float $amount, string $currency = 'NGN'): string
    {
        $symbols = [
            'NGN' => '₦',
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€'
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format($amount, 2);
    }

    public static function formatPercentage(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals) . '%';
    }

    public static function formatShares(int $shares): string
    {
        return number_format($shares);
    }
}