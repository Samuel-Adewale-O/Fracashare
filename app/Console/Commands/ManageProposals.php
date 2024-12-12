<?php

namespace App\Console\Commands;

use App\Models\Proposal;
use App\Notifications\ProposalEnded;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ManageProposals extends Command
{
    protected $signature = 'proposals:manage';
    protected $description = 'Manage proposal statuses and send notifications';

    public function handle()
    {
        $this->info('Starting proposal management...');

        try {
            // End expired proposals
            $expiredProposals = Proposal::where('status', 'active')
                ->where('voting_ends_at', '<=', now())
                ->get();

            foreach ($expiredProposals as $proposal) {
                $this->endProposal($proposal);
            }

            // Activate scheduled proposals
            $scheduledProposals = Proposal::where('status', 'draft')
                ->where('voting_starts_at', '<=', now())
                ->get();

            foreach ($scheduledProposals as $proposal) {
                $this->activateProposal($proposal);
            }

            $this->info('Proposal management completed successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Proposal management failed: ' . $e->getMessage());
            Log::error('Proposal management command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function endProposal(Proposal $proposal): void
    {
        $proposal->update(['status' => 'ended']);
        
        // Get voting results
        $results = $proposal->getVotingResults();
        
        // Notify asset shareholders
        $shareholders = $proposal->asset->investors()->get();
        Notification::send($shareholders, new ProposalEnded($proposal, $results));
        
        Log::info('Proposal ended', [
            'proposal_id' => $proposal->id,
            'title' => $proposal->title,
            'results' => $results
        ]);
    }

    private function activateProposal(Proposal $proposal): void
    {
        $proposal->update(['status' => 'active']);
        
        // Notify shareholders about new active proposal
        $shareholders = $proposal->asset->investors()->get();
        Notification::send($shareholders, new ProposalActivated($proposal));
        
        Log::info('Proposal activated', [
            'proposal_id' => $proposal->id,
            'title' => $proposal->title
        ]);
    }
}