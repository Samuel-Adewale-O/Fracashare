<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalEnded extends Notification implements ShouldQueue
{
    use Queueable;

    private Proposal $proposal;
    private array $results;

    public function __construct(Proposal $proposal, array $results)
    {
        $this->proposal = $proposal;
        $this->results = $results;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $approvalPercentage = number_format($this->results['approval_percentage'], 2);

        return (new MailMessage)
            ->subject("Voting Ended: {$this->proposal->title}")
            ->line("The voting period for the proposal '{$this->proposal->title}' has ended.")
            ->line("Final Results:")
            ->line("- Total Votes: {$this->results['total_voting_power']}")
            ->line("- Votes For: {$this->results['votes_for']}")
            ->line("- Votes Against: {$this->results['votes_against']}")
            ->line("- Approval Rate: {$approvalPercentage}%")
            ->action('View Details', url("/proposals/{$this->proposal->id}"));
    }

    public function toArray($notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'title' => $this->proposal->title,
            'asset_id' => $this->proposal->asset_id,
            'results' => $this->results
        ];
    }
}