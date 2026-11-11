<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorEarningLog extends Model
{
    use HasFactory;

    protected $table = 'creator_earning_logs';

    public $timestamps = false; // Only created_at in schema

    protected $fillable = [
        'creator_id',
        'drama_id',
        'episode_id',
        'duration_seconds',
        'blocks_count',
        'rate_per_block',
        'amount',
        'earning_type',
        'description',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'created_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function drama(): BelongsTo
    {
        return $this->belongsTo(Drama::class, 'drama_id');
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'episode_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->earning_type) {
            'paywall' => 'Jualan Episod (Paywall)',
            'season_pass' => 'Pakej Musim (Bundle)',
            'tip' => 'Tip / Hadiah Peminat',
            default => 'Kongsi Hasil Iklan',
        };
    }
}

