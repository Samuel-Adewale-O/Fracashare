<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\PaymentService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private TransactionService $transactionService
    ) {}

    public function initiate(Transaction $transaction)
    {
        try {
            $paymentData = $this->paymentService->initializePayment($transaction);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment initialized successfully',
                'data' => [
                    'authorization_url' => $paymentData['authorization_url'],
                    'access_code' => $paymentData['access_code'],
                    'reference' => $paymentData['reference'],
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function callback(Request $request)
    {
        try {
            $paymentData = $this->paymentService->verifyPayment($request->reference);
            
            if ($paymentData['status'] === 'success') {
                $transaction = Transaction::where('reference', $request->reference)->firstOrFail();
                $this->transactionService->completeTransaction($transaction);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment verified successfully',
                    'data' => $paymentData
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Payment verification failed'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function webhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('x-paystack-signature');
        $computedSignature = hash_hmac('sha512', $request->getContent(), config('paystack.secretKey'));

        if ($signature !== $computedSignature) {
            return response()->json(['status' => 'error'], 400);
        }

        // Process webhook event
        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'charge.success') {
            $transaction = Transaction::where('reference', $data['reference'])->first();
            
            if ($transaction && $transaction->status === 'pending') {
                $this->transactionService->completeTransaction($transaction);
            }
        }

        return response()->json(['status' => 'success']);
    }
}