<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'bvn',
        'nin',
        'kyc_status',
        'kyc_attempts',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function assetShares(): HasMany
    {
        return $this->hasMany(AssetShare::class);
    }

    public function investments(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_shares')
            ->withPivot('shares_owned', 'purchase_price', 'purchased_at')
            ->withTimestamps();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function dividendPayments(): HasMany
    {
        return $this->hasMany(DividendPayment::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function getTotalInvestmentValue(): float
    {
        return $this->assetShares()
            ->join('assets', 'asset_shares.asset_id', '=', 'assets.id')
            ->selectRaw('SUM(asset_shares.shares_owned * assets.share_price) as total_value')
            ->value('total_value') ?? 0;
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}