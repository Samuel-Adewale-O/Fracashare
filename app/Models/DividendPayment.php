<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DividendPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dividend_distribution_id',
        'user_id',
        'asset_share_id',
        'amount',
        'reference',
        'status',
        'failure_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(DividendDistribution::class, 'dividend_distribution_id');
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