<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

class OtpManager
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 10;
    private const MAX_ATTEMPTS = 3;

    public function generateOtp(): string
    {
        return str_pad((string) random_int(0, pow(10, self::OTP_LENGTH) - 1), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    public function storeOtp(User $user, string $otp): void
    {
        $cacheKey = $this->getCacheKey($user);
        Cache::put($cacheKey, [
            'otp' => $otp,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES)
        ], self::OTP_EXPIRY_MINUTES * 60);
    }

    public function validateOtp(User $user, string $otp): array
    {
        $cacheKey = $this->getCacheKey($user);
        $otpData = Cache::get($cacheKey);

        if (!$otpData) {
            return [
                'success' => false,
                'message' => 'OTP has expired or is invalid'
            ];
        }

        if ($otpData['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget($cacheKey);
            return [
                'success' => false,
                'message' => 'Maximum verification attempts reached'
            ];
        }

        $otpData['attempts']++;
        Cache::put($cacheKey, $otpData, self::OTP_EXPIRY_MINUTES * 60);

        if ($otpData['otp'] !== $otp) {
            return [
                'success' => false,
                'message' => 'Invalid OTP',
                'attempts_remaining' => self::MAX_ATTEMPTS - $otpData['attempts']
            ];
        }

        Cache::forget($cacheKey);
        return [
            'success' => true,
            'message' => 'OTP verified successfully'
        ];
    }

    public function getCacheKey(User $user): string
    {
        return "otp_data_{$user->id}";
    }

    public function getExpiryMinutes(): int
    {
        return self::OTP_EXPIRY_MINUTES;
    }

    public function getMaxAttempts(): int
    {
        return self::MAX_ATTEMPTS;
    }
}