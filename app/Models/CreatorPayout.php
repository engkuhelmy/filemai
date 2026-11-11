<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorPayout extends Model
{
    use HasFactory;

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

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'completed', 'approved' => [
                'label' => 'Selesai Dibayar',
                'badge' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            ],
            'rejected' => [
                'label' => 'Ditolak',
                'badge' => 'bg-red-500/20 text-red-400 border-red-500/30',
            ],
            default => [
                'label' => 'Sedang Diproses',
                'badge' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            ],
        };
    }
}

