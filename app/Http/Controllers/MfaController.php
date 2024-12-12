<?php

namespace App\Http\Controllers;

use App\Services\OtpService;
use App\Http\Requests\VerifyOtpRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MfaController extends Controller
{
    private OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->middleware(['auth:sanctum']);
        $this->otpService = $otpService;
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->otpService->generateAndSendOtp($user);

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => [
                'expires_in' => $result['expires_in'] ?? null
            ]
        ], $result['success'] ? 200 : 400);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->otpService->verifyOtp($user, $request->otp);

        return response()->json([
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'data' => [
                'attempts_remaining' => $result['attempts_remaining'] ?? null
            ]
        ], $result['success'] ? 200 : 400);
    }
}