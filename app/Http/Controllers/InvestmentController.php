<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\TransactionService;
use App\Http\Requests\InvestmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestmentController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {
        $this->middleware(['auth:sanctum']);
    }

    public function invest(InvestmentRequest $request, Asset $asset)
    {
        try {
            $transaction = $this->transactionService->initiateInvestment(
                $request->user(),
                $asset,
                $request->shares
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Investment initiated successfully',
                'data' => [
                    'transaction' => $transaction,
                    'payment_url' => route('payment.initiate', $transaction)
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function portfolio(Request $request)
    {
        $user = $request->user();
        
        $investments = $user->investments()
            ->with(['shares' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->get()
            ->map(function ($asset) {
                return [
                    'asset' => [
                        'id' => $asset->id,
                        'name' => $asset->name,
                        'type' => $asset->type,
                        'share_price' => $asset->share_price,
                        'expected_roi' => $asset->expected_roi
                    ],
                    'shares_owned' => $asset->pivot->shares_owned,
                    'purchase_price' => $asset->pivot->purchase_price,
                    'current_value' => $asset->pivot->shares_owned * $asset->share_price,
                    'ownership_percentage' => $asset->calculateOwnershipPercentage($user),
                    'purchased_at' => $asset->pivot->purchased_at
                ];
            });

        $portfolio = [
            'total_value' => $user->getTotalInvestmentValue(),
            'investments' => $investments,
            'investment_history' => $user->transactions()
                ->select(DB::raw('DATE(created_at) as date'))
                ->selectRaw('SUM(amount) as daily_investment')
                ->where('status', 'completed')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $portfolio
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = $request->user()
            ->transactions()
            ->with('asset:id,name,type')
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }
}