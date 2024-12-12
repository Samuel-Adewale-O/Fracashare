<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\DividendDistribution;
use App\Models\DividendPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class DividendService
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createDistribution(
        Asset $asset,
        float $totalAmount,
        string $description = null,
        array $metadata = null
    ): DividendDistribution {
        if ($totalAmount <= 0) {
            throw new Exception('Dividend amount must be greater than zero.');
        }

        return DB::transaction(function () use ($asset, $totalAmount, $description, $metadata) {
            $amountPerShare = $totalAmount / $asset->total_shares;

            return DividendDistribution::create([
                'asset_id' => $asset->id,
                'total_amount' => $totalAmount,
                'amount_per_share' => $amountPerShare,
                'distribution_date' => now(),
                'description' => $description,
                'metadata' => $metadata
            ]);
        });
    }

    public function processDistribution(DividendDistribution $distribution): void
    {
        if ($distribution->status !== 'pending') {
            throw new Exception('Distribution has already been processed.');
        }

        DB::transaction(function () use ($distribution) {
            $distribution->update(['status' => 'processing']);

            $shares = $distribution->asset->shares()->with('user')->get();

            foreach ($shares as $share) {
                $amount = $distribution->calculatePaymentForShares($share->shares_owned);

                DividendPayment::create([
                    'dividend_distribution_id' => $distribution->id,
                    'user_id' => $share->user_id,
                    'asset_share_id' => $share->id,
                    'amount' => $amount,
                    'reference' => 'DIV-' . strtoupper(Str::random(10)),
                    'status' => 'pending'
                ]);
            }

            $distribution->update(['status' => 'completed']);
        });
    }

    public function processPayment(DividendPayment $payment): void
    {
        if ($payment->status !== 'pending') {
            throw new Exception('Payment has already been processed.');
        }

        try {
            DB::transaction(function () use ($payment) {
                $payment->update(['status' => 'processing']);

                // Initialize payment through payment service
                $paymentData = $this->paymentService->initializePayment([
                    'amount' => $payment->amount,
                    'reference' => $payment->reference,
                    'user' => $payment->user,
                    'metadata' => [
                        'type' => 'dividend',
                        'distribution_id' => $payment->dividend_distribution_id,
                        'asset_id' => $payment->distribution->asset_id
                    ]
                ]);

                if ($paymentData['status'] === 'success') {
                    $payment->update(['status' => 'completed']);
                } else {
                    throw new Exception('Payment initialization failed');
                }
            });
        } catch (Exception $e) {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function getDividendHistory(Asset $asset)
    {
        return DividendDistribution::where('asset_id', $asset->id)
            ->with(['payments' => function ($query) {
                $query->select('id', 'dividend_distribution_id', 'amount', 'status')
                    ->selectRaw('COUNT(*) as total_payments')
                    ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_payments')
                    ->groupBy('dividend_distribution_id');
            }])
            ->orderByDesc('distribution_date')
            ->get();
    }

    public function getUserDividends(int $userId)
    {
        return DividendPayment::where('user_id', $userId)
            ->with(['distribution.asset:id,name'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('distribution.asset.name')
            ->map(function ($payments) {
                return [
                    'total_amount' => $payments->sum('amount'),
                    'payments' => $payments->map(function ($payment) {
                        return [
                            'amount' => $payment->amount,
                            'date' => $payment->created_at,
                            'status' => $payment->status
                        ];
                    })
                ];
            });
    }
}