<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Http;
use Exception;

class SmsService
{
    private const TERMII_BASE_URL = 'https://api.ng.termii.com/api';
    private string $apiKey;
    private string $senderId;

    public function __construct()
    {
        $this->apiKey = config('services.termii.api_key');
        $this->senderId = config('services.termii.sender_id');
    }

    public function sendSms(string $phoneNumber, string $message): array
    {
        try {
            $response = Http::post(self::TERMII_BASE_URL . '/sms/send', [
                'api_key' => $this->apiKey,
                'to' => $phoneNumber,
                'from' => $this->senderId,
                'sms' => $message,
                'type' => 'plain',
                'channel' => 'generic'
            ]);

            if (!$response->successful()) {
                throw new Exception('Failed to send SMS: ' . $response->json('message'));
            }

            return [
                'success' => true,
                'message' => 'SMS sent successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}