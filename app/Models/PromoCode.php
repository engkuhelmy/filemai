<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCode extends Model
{
    use HasFactory;

    protected $table = 'promo_codes';

    protected $fillable = [
        'creator_id',
        'drama_id',
        'code',
        'discount_type',
        'discount_value',
        'usage_limit',
        'times_used',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'times_used' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function drama(): BelongsTo
    {
        return $this->belongsTo(Drama::class, 'drama_id');
    }
}
