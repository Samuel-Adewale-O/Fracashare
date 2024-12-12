<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'user_id',
        'title',
        'description',
        'category',
        'status',
        'voting_starts_at',
        'voting_ends_at',
        'metadata'
    ];

    protected $casts = [
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function getVotingResults(): array
    {
        $totalVotingPower = $this->votes()->sum('voting_power');
        $votesFor = $this->votes()->where('vote', true)->sum('voting_power');
        $votesAgainst = $this->votes()->where('vote', false)->sum('voting_power');

        return [
            'total_voting_power' => $totalVotingPower,
            'votes_for' => $votesFor,
            'votes_against' => $votesAgainst,
            'approval_percentage' => $totalVotingPower > 0 ? ($votesFor / $totalVotingPower) * 100 : 0
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('voting_starts_at', '<=', now())
            ->where('voting_ends_at', '>=', now());
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended')
            ->orWhere('voting_ends_at', '<', now());
    }
}