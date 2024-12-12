<?php

namespace App\Services;

use App\Models\User;
use App\Services\Security\OtpManager;
use App\Services\Security\SmsService;
use Exception;

class OtpService
{
    private OtpManager $otpManager;
    private SmsService $smsService;

    public function __construct(OtpManager $otpManager, SmsService $smsService)
    {
        $this->otpManager = $otpManager;
        $this->smsService = $smsService;
    }

    public function generateAndSendOtp(User $user): array
    {
        try {
            $otp = $this->otpManager->generateOtp();
            $message = "Your Fracashare verification code is: {$otp}. Valid for {$this->otpManager->getExpiryMinutes()} minutes.";

            $smsResult = $this->smsService->sendSms($user->phone, $message);
            
            if (!$smsResult['success']) {
                throw new Exception($smsResult['message']);
            }

            $this->otpManager->storeOtp($user, $otp);

            return [
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_in' => $this->otpManager->getExpiryMinutes() * 60
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function verifyOtp(User $user, string $otp): array
    {
        return $this->otpManager->validateOtp($user, $otp);
    }
}