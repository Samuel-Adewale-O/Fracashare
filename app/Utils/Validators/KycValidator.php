<?php

namespace App\Utils\Validators;

class KycValidator
{
    public static function validateBVN(string $bvn): bool
    {
        return strlen($bvn) === 11 && is_numeric($bvn);
    }

    public static function validateNIN(string $nin): bool
    {
        return strlen($nin) === 11 && is_numeric($nin);
    }

    public static function validatePhoneNumber(string $phone): bool
    {
        // Nigerian phone number format validation
        return preg_match('/^(\+234|0)[789][01]\d{8}$/', $phone);
    }
}