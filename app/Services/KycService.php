<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class KycService
{
    private const VERIFY_ME_BASE_URL = 'https://api.verify.me/v1';
    private const SMILE_IDENTITY_BASE_URL = 'https://api.smileidentity.com/v1';

    private string $verifyMeApiKey;
    private string $smileIdentityApiKey;
    private string $smileIdentityPartnerId;

    public function __construct()
    {
        $this->verifyMeApiKey = config('services.verify_me.api_key');
        $this->smileIdentityApiKey = config('services.smile_identity.api_key');
        $this->smileIdentityPartnerId = config('services.smile_identity.partner_id');
    }

    public function verifyBvn(User $user, string $bvn): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->verifyMeApiKey}",
                'Content-Type' => 'application/json',
            ])->post(self::VERIFY_ME_BASE_URL . '/verifications/bvn', [
                'bvn' => $bvn,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'phoneNumber' => $user->phone
            ]);

            if (!$response->successful()) {
                throw new Exception('BVN verification failed: ' . $response->json('message'));
            }

            return [
                'success' => true,
                'data' => $response->json('data'),
                'provider' => 'verify_me'
            ];
        } catch (Exception $e) {
            Log::error('BVN verification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'provider' => 'verify_me'
            ];
        }
    }

    public function verifyNin(User $user, string $nin): array
    {
        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->smileIdentityApiKey,
                'X-Partner-Id' => $this->smileIdentityPartnerId,
                'Content-Type' => 'application/json',
            ])->post(self::SMILE_IDENTITY_BASE_URL . '/kyc/nin', [
                'nin' => $nin,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone_number' => $user->phone
            ]);

            if (!$response->successful()) {
                throw new Exception('NIN verification failed: ' . $response->json('message'));
            }

            return [
                'success' => true,
                'data' => $response->json('data'),
                'provider' => 'smile_identity'
            ];
        } catch (Exception $e) {
            Log::error('NIN verification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'provider' => 'smile_identity'
            ];
        }
    }

    public function processKycVerification(User $user, array $data): bool
    {
        $user->increment('kyc_attempts');

        try {
            $bvnVerification = isset($data['bvn']) ? $this->verifyBvn($user, $data['bvn']) : null;
            $ninVerification = isset($data['nin']) ? $this->verifyNin($user, $data['nin']) : null;

            $isVerified = ($bvnVerification['success'] ?? false) && ($ninVerification['success'] ?? false);

            $user->update([
                'bvn' => $data['bvn'] ?? null,
                'nin' => $data['nin'] ?? null,
                'kyc_status' => $isVerified ? 'verified' : 'failed'
            ]);

            Log::info('KYC verification processed', [
                'user_id' => $user->id,
                'status' => $user->kyc_status,
                'attempts' => $user->kyc_attempts
            ]);

            return $isVerified;
        } catch (Exception $e) {
            Log::error('KYC verification processing failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            $user->update(['kyc_status' => 'failed']);
            return false;
        }
    }
}