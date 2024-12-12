<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_staff_reply'
    ];

    protected $casts = [
        'is_staff_reply' => 'boolean'
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}