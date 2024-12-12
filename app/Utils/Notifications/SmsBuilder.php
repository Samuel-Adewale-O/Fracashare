<?php

namespace App\Utils\Notifications;

class SmsBuilder
{
    public static function buildOtpMessage(string $otp, int $expiryMinutes): string
    {
        return "Your Fracashare verification code is: {$otp}. Valid for {$expiryMinutes} minutes.";
    }

    public static function buildTransactionMessage(string $type, float $amount, string $reference): string
    {
        $formattedAmount = number_format($amount, 2);
        return "Your {$type} transaction of NGN {$formattedAmount} (Ref: {$reference}) has been processed successfully.";
    }

    public static function buildDividendMessage(float $amount, string $assetName): string
    {
        $formattedAmount = number_format($amount, 2);
        return "You have received a dividend payment of NGN {$formattedAmount} from your investment in {$assetName}.";
    }
}