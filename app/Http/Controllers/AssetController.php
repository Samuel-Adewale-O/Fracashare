<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Http\Requests\AssetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware(['role:admin|asset_manager'])->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $assets = Asset::query()
            ->when($request->type, fn($query, $type) => $query->where('type', $type))
            ->when($request->status, fn($query, $status) => $query->where('status', $status))
            ->when($request->risk_level, fn($query, $risk) => $query->where('risk_level', $risk))
            ->when($request->min_roi, fn($query, $roi) => $query->where('expected_roi', '>=', $roi))
            ->when($request->max_investment, fn($query, $max) => $query->where('minimum_investment', '<=', $max))
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $assets
        ]);
    }

    public function store(AssetRequest $request)
    {
        $asset = DB::transaction(function () use ($request) {
            $asset = Asset::create($request->validated());
            
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('assets', 'public');
                    $metadata = $asset->metadata ?? [];
                    $metadata['images'][] = $path;
                    $asset->update(['metadata' => $metadata]);
                }
            }

            return $asset;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Asset created successfully',
            'data' => $asset
        ], 201);
    }

    public function show(Asset $asset)
    {
        $asset->load(['investors' => function ($query) {
            $query->select('users.id', 'first_name', 'last_name', 'email')
                  ->withPivot('shares_owned', 'purchase_price', 'purchased_at');
        }]);

        return response()->json([
            'status' => 'success',
            'data' => $asset
        ]);
    }

    public function update(AssetRequest $request, Asset $asset)
    {
        if ($asset->status === 'closed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update a closed asset'
            ], 400);
        }

        $asset->update($request->validated());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('assets', 'public');
                $metadata = $asset->metadata ?? [];
                $metadata['images'][] = $path;
                $asset->update(['metadata' => $metadata]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Asset updated successfully',
            'data' => $asset
        ]);
    }

    public function destroy(Asset $asset)
    {
        if ($asset->shares()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete an asset with existing shares'
            ], 400);
        }

        $asset->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Asset deleted successfully'
        ]);
    }

    public function analytics(Asset $asset)
    {
        $analytics = [
            'total_investors' => $asset->investors()->count(),
            'total_invested' => $asset->shares()->sum(DB::raw('shares_owned * purchase_price')),
            'ownership_distribution' => $asset->investors()
                ->select('users.id', 'first_name', 'last_name')
                ->selectRaw('(SUM(shares_owned) / ?) * 100 as ownership_percentage', [$asset->total_shares])
                ->groupBy('users.id', 'first_name', 'last_name')
                ->having('ownership_percentage', '>', 0)
                ->get(),
            'investment_history' => $asset->shares()
                ->select(DB::raw('DATE(purchased_at) as date'))
                ->selectRaw('SUM(shares_owned * purchase_price) as daily_investment')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        ];

        return response()->json([
            'status' => 'success',
            'data' => $analytics
        ]);
    }
}