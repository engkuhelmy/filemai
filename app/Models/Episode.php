<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\YouTubeService;

class Episode extends Model
{
    use HasFactory;

    protected $table = 'episodes';

    protected $fillable = [
        'drama_id',
        'episode_number',
        'title',
        'video_source',
        'youtube_video_id',
        'video_url',
        'duration',
        'views_count',
        'is_free_preview',
    ];

    protected $casts = [
        'episode_number' => 'integer',
        'duration' => 'integer',
        'views_count' => 'integer',
        'is_free_preview' => 'boolean',
    ];

    /**
     * Relationship: The parent drama series.
     */
    public function drama(): BelongsTo
    {
        return $this->belongsTo(Drama::class, 'drama_id');
    }

    /**
     * Check if this episode is sourced from YouTube.
     */
    public function isYouTube(): bool
    {
        return $this->video_source === 'youtube' && !empty($this->youtube_video_id);
    }

    /**
     * Get high-quality thumbnail preview URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->isYouTube()) {
            return YouTubeService::getThumbnailUrl($this->youtube_video_id);
        }
        return $this->drama->poster_url ?? '/uploads/posters/default.jpg';
    }

    /**
     * Formatted duration string (mm:ss).
     */
    public function getFormattedDurationAttribute(): string
    {
        $duration = $this->duration ?? 60;
        return gmdate('i:s', $duration);
    }
}
