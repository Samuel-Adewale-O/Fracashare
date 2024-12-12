<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Proposal;
use App\Http\Requests\ProposalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    public function index(Request $request)
    {
        $proposals = Proposal::query()
            ->when($request->asset_id, fn($q) => $q->where('asset_id', $request->asset_id))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with(['asset:id,name', 'user:id,first_name,last_name'])
            ->withCount('votes')
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $proposals
        ]);
    }

    public function store(ProposalRequest $request)
    {
        $asset = Asset::findOrFail($request->asset_id);
        
        // Check if user owns shares in the asset
        $userShares = $asset->shares()
            ->where('user_id', $request->user()->id)
            ->exists();

        if (!$userShares && !$request->user()->hasRole(['admin', 'asset_manager'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only shareholders can create proposals'
            ], 403);
        }

        $proposal = Proposal::create(array_merge(
            $request->validated(),
            ['user_id' => $request->user()->id]
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal created successfully',
            'data' => $proposal->load(['asset:id,name', 'user:id,first_name,last_name'])
        ], 201);
    }

    public function show(Proposal $proposal)
    {
        $proposal->load([
            'asset:id,name,total_shares',
            'user:id,first_name,last_name',
            'votes' => fn($q) => $q->with('user:id,first_name,last_name')
        ]);

        $votingResults = $proposal->getVotingResults();

        return response()->json([
            'status' => 'success',
            'data' => array_merge(
                $proposal->toArray(),
                ['voting_results' => $votingResults]
            )
        ]);
    }

    public function vote(Proposal $proposal, Request $request)
    {
        if ($proposal->status !== 'active' || now()->isAfter($proposal->voting_ends_at)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voting period has ended'
            ], 400);
        }

        $request->validate([
            'vote' => 'required|boolean',
            'comment' => 'nullable|string'
        ]);

        $userShares = $proposal->asset->shares()
            ->where('user_id', $request->user()->id)
            ->get();

        if ($userShares->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only shareholders can vote'
            ], 403);
        }

        DB::transaction(function () use ($proposal, $request, $userShares) {
            foreach ($userShares as $share) {
                $proposal->votes()->updateOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'asset_share_id' => $share->id
                    ],
                    [
                        'vote' => $request->vote,
                        'voting_power' => $share->shares_owned,
                        'comment' => $request->comment
                    ]
                );
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Vote recorded successfully',
            'data' => $proposal->getVotingResults()
        ]);
    }

    public function update(ProposalRequest $request, Proposal $proposal)
    {
        if ($proposal->status !== 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft proposals can be updated'
            ], 400);
        }

        if ($proposal->user_id !== $request->user()->id && !$request->user()->hasRole(['admin', 'asset_manager'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this proposal'
            ], 403);
        }

        $proposal->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal updated successfully',
            'data' => $proposal->load(['asset:id,name', 'user:id,first_name,last_name'])
        ]);
    }

    public function destroy(Proposal $proposal, Request $request)
    {
        if ($proposal->status !== 'draft' && !$request->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only draft proposals can be deleted'
            ], 400);
        }

        if ($proposal->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this proposal'
            ], 403);
        }

        $proposal->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal deleted successfully'
        ]);
    }
}