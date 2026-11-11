<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorWallet extends Model
{
    use HasFactory;

    protected $table = 'creator_wallets';

    protected $fillable = [
        'user_id',
        'total_earned',
        'total_withdrawn',
        'current_balance',
        'pending_balance',
        'total_blocks',
    ];

    protected $casts = [
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Amount ready for withdrawal (Current balance minus pending payout requests).
     */
    public function getAvailableForWithdrawalAttribute(): float
    {
        $pendingPayouts = CreatorPayout::where('creator_id', $this->user_id)
            ->where('status', 'pending')
            ->sum('amount');

        return max(0, (float)$this->current_balance - (float)$pendingPayouts);
    }
}
