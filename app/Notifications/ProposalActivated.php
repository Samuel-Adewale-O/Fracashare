<?php

namespace App\Notifications;

use App\Models\Proposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProposalActivated extends Notification implements ShouldQueue
{
    use Queueable;

    private Proposal $proposal;

    public function __construct(Proposal $proposal)
    {
        $this->proposal = $proposal;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Proposal: {$this->proposal->title}")
            ->line("A new proposal for {$this->proposal->asset->name} is now open for voting.")
            ->line("Title: {$this->proposal->title}")
            ->line("Category: {$this->proposal->category}")
            ->line("Voting ends at: {$this->proposal->voting_ends_at->format('Y-m-d H:i:s')}")
            ->action('Vote Now', url("/proposals/{$this->proposal->id}"))
            ->line('Your vote is important for the democratic decision-making process.');
    }

    public function toArray($notifiable): array
    {
        return [
            'proposal_id' => $this->proposal->id,
            'title' => $this->proposal->title,
            'asset_id' => $this->proposal->asset_id,
            'voting_ends_at' => $this->proposal->voting_ends_at
        ];
    }
}