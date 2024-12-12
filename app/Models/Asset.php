<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'total_value',
        'minimum_investment',
        'total_shares',
        'available_shares',
        'share_price',
        'expected_roi',
        'risk_level',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'total_value' => 'decimal:2',
        'minimum_investment' => 'decimal:2',
        'share_price' => 'decimal:2',
        'expected_roi' => 'decimal:2'
    ];

    public function shares(): HasMany
    {
        return $this->hasMany(AssetShare::class);
    }

    public function investors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'asset_shares')
            ->withPivot('shares_owned', 'purchase_price', 'purchased_at')
            ->withTimestamps();
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function calculateOwnershipPercentage(User $user): float
    {
        $userShares = $this->shares()
            ->where('user_id', $user->id)
            ->sum('shares_owned');

        return ($userShares / $this->total_shares) * 100;
    }
}