<?php

namespace App\Http\Controllers;

use App\Services\KycService;
use App\Http\Requests\KycVerificationRequest;
use Illuminate\Http\Request;

class KycController extends Controller
{
    private KycService $kycService;

    public function __construct(KycService $kycService)
    {
        $this->middleware(['auth:sanctum']);
        $this->kycService = $kycService;
    }

    public function verify(KycVerificationRequest $request)
    {
        $user = $request->user();

        if ($user->kyc_status === 'verified') {
            return response()->json([
                'status' => 'error',
                'message' => 'User is already verified'
            ], 400);
        }

        if ($user->kyc_attempts >= 3) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maximum verification attempts reached. Please contact support.'
            ], 400);
        }

        $isVerified = $this->kycService->processKycVerification($user, $request->validated());

        return response()->json([
            'status' => $isVerified ? 'success' : 'error',
            'message' => $isVerified ? 'KYC verification successful' : 'KYC verification failed',
            'data' => [
                'kyc_status' => $user->kyc_status,
                'attempts_remaining' => 3 - $user->kyc_attempts
            ]
        ], $isVerified ? 200 : 400);
    }

    public function status(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'kyc_status' => $user->kyc_status,
                'attempts' => $user->kyc_attempts,
                'attempts_remaining' => 3 - $user->kyc_attempts,
                'has_bvn' => !empty($user->bvn),
                'has_nin' => !empty($user->nin)
            ]
        ]);
    }
}