<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FanTip extends Model
{
    use HasFactory;

    protected $table = 'fan_tips';

    protected $fillable = [
        'creator_id',
        'drama_id',
        'episode_id',
        'user_id',
        'fan_name',
        'fan_email',
        'amount',
        'message',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
}

