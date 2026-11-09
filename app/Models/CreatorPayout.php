<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorPayout extends Model
{
    protected $table = 'creator_payouts';

    protected $fillable = [
        'creator_id',
        'amount',
        'bank_name',
        'account_number',
        'account_holder',
        'status',
        'reference_no',
        'notes',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'RM ' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'completed' => [
                'label' => 'Selesai Dibayar',
                'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                'icon' => 'check-circle',
            ],
            'approved' => [
                'label' => 'Diluluskan',
                'class' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                'icon' => 'clock',
            ],
            'rejected' => [
                'label' => 'Ditolak',
                'class' => 'bg-red-500/10 text-red-400 border-red-500/30',
                'icon' => 'x-circle',
            ],
            default => [
                'label' => 'Menunggu Semakan',
                'class' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                'icon' => 'hourglass',
            ],
        };
    }
}

