<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorEarningLog extends Model
{
    protected $table = 'creator_earning_logs';

    public $timestamps = false;

    protected $fillable = [
        'creator_id',
        'drama_id',
        'episode_id',
        'duration_seconds',
        'blocks_count',
        'rate_per_block',
        'amount',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'blocks_count' => 'integer',
            'rate_per_block' => 'decimal:6',
            'amount' => 'decimal:6',
            'created_at' => 'datetime',
        ];
    }

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
}
