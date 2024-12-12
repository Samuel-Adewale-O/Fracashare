<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DividendDistribution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'total_amount',
        'amount_per_share',
        'status',
        'distribution_date',
        'description',
        'metadata'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_per_share' => 'decimal:2',
        'distribution_date' => 'datetime',
        'metadata' => 'array'
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DividendPayment::class);
    }

    public function calculatePaymentForShares(int $sharesOwned): float
    {
        return $this->amount_per_share * $sharesOwned;
    }
}