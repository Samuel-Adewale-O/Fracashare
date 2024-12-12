<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    private const TRANSACTION_FEE_PERCENTAGE = 1.5;

    public function initiateInvestment(User $user, Asset $asset, int $shares): Transaction
    {
        $this->validateInvestment($user, $asset, $shares);

        $amount = $shares * $asset->share_price;
        $fee = $this->calculateTransactionFee($amount);

        return DB::transaction(function () use ($user, $asset, $shares, $amount, $fee) {
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'asset_id' => $asset->id,
                'reference' => $this->generateReference(),
                'type' => 'buy',
                'shares' => $shares,
                'amount' => $amount,
                'fee' => $fee,
                'status' => 'pending'
            ]);

            $asset->decrement('available_shares', $shares);

            return $transaction;
        });
    }

    public function completeTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => 'completed']);

            if ($transaction->type === 'buy') {
                $transaction->asset->shares()->create([
                    'user_id' => $transaction->user_id,
                    'shares_owned' => $transaction->shares,
                    'purchase_price' => $transaction->amount,
                    'purchased_at' => now()
                ]);
            }
        });
    }

    private function validateInvestment(User $user, Asset $asset, int $shares): void
    {
        if ($asset->status !== 'active') {
            throw new Exception('This asset is not available for investment.');
        }

        if ($shares <= 0) {
            throw new Exception('Number of shares must be greater than zero.');
        }

        if ($shares > $asset->available_shares) {
            throw new Exception('Requested shares exceed available shares.');
        }

        $investmentAmount = $shares * $asset->share_price;
        if ($investmentAmount < $asset->minimum_investment) {
            throw new Exception("Minimum investment amount is {$asset->minimum_investment} NGN.");
        }

        if ($user->kyc_status !== 'verified') {
            throw new Exception('KYC verification is required for investment.');
        }
    }

    private function calculateTransactionFee(float $amount): float
    {
        return round($amount * (self::TRANSACTION_FEE_PERCENTAGE / 100), 2);
    }

    private function generateReference(): string
    {
        return 'TRX-' . strtoupper(Str::random(10));
    }
}