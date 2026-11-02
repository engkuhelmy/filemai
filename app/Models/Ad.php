<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Ad extends Model
{
    use HasFactory;

    protected $table = 'ads';

    protected $fillable = [
        'title',
        'video_url',
        'duration_seconds',
        'click_url',
        'sponsor_name',
        'is_active',
        'views_count',
        'clicks_count',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'is_active' => 'boolean',
        'views_count' => 'integer',
        'clicks_count' => 'integer',
    ];

    /**
     * Scope: Only active advertisement campaigns.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Fetch a single random active interstitial ad for playback gating.
     */
    public static function getRandomActiveAd(): ?self
    {
        return static::active()->inRandomOrder()->first();
    }
}
