<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\User;
use App\Models\Transaction;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    public function overview()
    {
        $stats = Cache::remember('admin_dashboard_stats', 1800, function () {
            return [
                'total_users' => User::count(),
                'verified_users' => User::where('kyc_status', 'verified')->count(),
                'total_assets' => Asset::count(),
                'active_assets' => Asset::where('status', 'active')->count(),
                'total_investment' => Transaction::where('status', 'completed')
                    ->where('type', 'buy')
                    ->sum('amount'),
                'open_tickets' => SupportTicket::open()->count(),
                'user_growth' => $this->getUserGrowthStats(),
                'investment_stats' => $this->getInvestmentStats(),
                'asset_distribution' => $this->getAssetDistribution(),
                'recent_activities' => $this->getRecentActivities()
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    private function getUserGrowthStats()
    {
        return User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as new_users')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($stat) => [
                'date' => $stat->date,
                'new_users' => $stat->new_users
            ]);
    }

    private function getInvestmentStats()
    {
        return Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('COUNT(*) as transaction_count')
        )
            ->where('status', 'completed')
            ->where('type', 'buy')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($stat) => [
                'date' => $stat->date,
                'total_amount' => $stat->total_amount,
                'transaction_count' => $stat->transaction_count
            ]);
    }

    private function getAssetDistribution()
    {
        return Asset::select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->map(fn($stat) => [
                'type' => $stat->type,
                'count' => $stat->count
            ]);
    }

    private function getRecentActivities()
    {
        return Transaction::with(['user:id,first_name,last_name', 'asset:id,name'])
            ->where('status', 'completed')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'user' => $transaction->user->full_name,
                'asset' => $transaction->asset->name,
                'amount' => $transaction->amount,
                'date' => $transaction->created_at
            ]);
    }

    public function userMetrics(Request $request)
    {
        $metrics = [
            'kyc_status_distribution' => User::select('kyc_status', DB::raw('COUNT(*) as count'))
                ->groupBy('kyc_status')
                ->get(),
            'top_investors' => User::withSum('transactions as total_invested', 'amount')
                ->whereHas('transactions', fn($q) => $q->where('status', 'completed')->where('type', 'buy'))
                ->orderByDesc('total_invested')
                ->limit(10)
                ->get(['id', 'first_name', 'last_name', 'email']),
            'user_activity' => User::withCount(['transactions', 'supportTickets'])
                ->orderByDesc('transactions_count')
                ->limit(10)
                ->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $metrics
        ]);
    }

    public function assetMetrics(Request $request)
    {
        $metrics = [
            'performance' => Asset::withSum('shares as total_invested', 'purchase_price')
                ->withCount('investors')
                ->orderByDesc('total_invested')
                ->get()
                ->map(fn($asset) => [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'type' => $asset->type,
                    'total_invested' => $asset->total_invested,
                    'investors_count' => $asset->investors_count,
                    'roi' => $asset->expected_roi
                ]),
            'risk_distribution' => Asset::select('risk_level', DB::raw('COUNT(*) as count'))
                ->groupBy('risk_level')
                ->get(),
            'investment_trends' => Transaction::select(
                'asset_id',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
                ->where('status', 'completed')
                ->where('type', 'buy')
                ->groupBy('asset_id')
                ->with('asset:id,name')
                ->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $metrics
        ]);
    }
}