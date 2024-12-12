<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'proposal_id',
        'user_id',
        'asset_share_id',
        'vote',
        'voting_power',
        'comment'
    ];

    protected $casts = [
        'vote' => 'boolean',
        'voting_power' => 'integer'
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assetShare(): BelongsTo
    {
        return $this->belongsTo(AssetShare::class);
    }
}