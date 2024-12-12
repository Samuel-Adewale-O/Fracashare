<?php

namespace App\Utils\Security;

use Illuminate\Support\Str;

class TokenGenerator
{
    public static function generateOTP(int $length = 6): string
    {
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    public static function generateApiKey(): string
    {
        return Str::random(32);
    }

    public static function generateTransactionReference(string $prefix = 'TRX'): string
    {
        return $prefix . '-' . strtoupper(Str::random(10));
    }
}