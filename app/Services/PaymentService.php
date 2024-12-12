<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Exception;

class PaymentService
{
    private string $baseUrl;
    private string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('paystack.paymentUrl');
        $this->secretKey = config('paystack.secretKey');
    }

    public function initializePayment(Transaction $transaction): array
    {
        $amount = ($transaction->amount + $transaction->fee) * 100; // Convert to kobo

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->secretKey}",
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/transaction/initialize", [
            'email' => $transaction->user->email,
            'amount' => $amount,
            'reference' => $transaction->reference,
            'callback_url' => route('payment.callback'),
            'metadata' => [
                'transaction_id' => $transaction->id,
                'asset_id' => $transaction->asset_id,
                'shares' => $transaction->shares,
            ],
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to initialize payment: ' . $response->json('message'));
        }

        return $response->json('data');
    }

    public function verifyPayment(string $reference): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->secretKey}",
            'Content-Type' => 'application/json',
        ])->get("{$this->baseUrl}/transaction/verify/{$reference}");

        if (!$response->successful()) {
            throw new Exception('Failed to verify payment: ' . $response->json('message'));
        }

        return $response->json('data');
    }
}