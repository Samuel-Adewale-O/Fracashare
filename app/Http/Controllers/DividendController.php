<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\DividendDistribution;
use App\Models\DividendPayment;
use App\Services\DividendService;
use Illuminate\Http\Request;

class DividendController extends Controller
{
    private DividendService $dividendService;

    public function __construct(DividendService $dividendService)
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware(['role:admin'])->only(['store', 'process']);
        $this->dividendService = $dividendService;
    }

    public function index(Request $request)
    {
        $distributions = DividendDistribution::query()
            ->when($request->asset_id, fn($q) => $q->where('asset_id', $request->asset_id))
            ->with(['asset:id,name', 'payments'])
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $distributions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'total_amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array'
        ]);

        try {
            $asset = Asset::findOrFail($request->asset_id);
            $distribution = $this->dividendService->createDistribution(
                $asset,
                $request->total_amount,
                $request->description,
                $request->metadata
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Dividend distribution created successfully',
                'data' => $distribution
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show(DividendDistribution $distribution)
    {
        $distribution->load([
            'asset:id,name',
            'payments.user:id,first_name,last_name,email'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $distribution
        ]);
    }

    public function process(DividendDistribution $distribution)
    {
        try {
            $this->dividendService->processDistribution($distribution);

            return response()->json([
                'status' => 'success',
                'message' => 'Dividend distribution processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function userDividends(Request $request)
    {
        $dividends = $this->dividendService->getUserDividends($request->user()->id);

        return response()->json([
            'status' => 'success',
            'data' => $dividends
        ]);
    }

    public function assetDividends(Asset $asset)
    {
        $history = $this->dividendService->getDividendHistory($asset);

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }
}