<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    private const CACHE_KEY = 'currency_rates';
    private const CACHE_TTL = 3600; // 1 hour

    public function getExchangeRates(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $response = Http::get('https://api.exchangerate-api.com/v4/latest/NGN');
            
            if ($response->successful()) {
                return $response->json()['rates'];
            }

            return [
                'USD' => 0.00133,
                'GBP' => 0.00106,
                'EUR' => 0.00124,
            ];
        });
    }

    public function convertToNGN(float $amount, string $fromCurrency): float
    {
        $rates = $this->getExchangeRates();
        $rate = $rates[$fromCurrency] ?? 1;
        
        return round($amount / $rate, 2);
    }

    public function convertFromNGN(float $amount, string $toCurrency): float
    {
        $rates = $this->getExchangeRates();
        $rate = $rates[$toCurrency] ?? 1;
        
        return round($amount * $rate, 2);
    }

    public function formatMoney(float $amount, string $currency = 'NGN'): string
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
}