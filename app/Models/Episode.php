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
        'video_type',
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
     * Accessor for normalized video_type ('youtube' or 'storage').
     */
    public function getVideoTypeAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        if (!empty($this->video_source)) {
            return $this->video_source === 'youtube' ? 'youtube' : 'storage';
        }
        if (!empty($this->youtube_video_id)) {
            return 'youtube';
        }
        return \App\Helpers\MediaHelper::detectSource($this->video_url);
    }

    /**
     * Check if this episode is sourced from YouTube.
     */
    public function isYouTube(): bool
    {
        return $this->video_type === 'youtube' || (!empty($this->video_source) && $this->video_source === 'youtube') || !empty($this->effective_youtube_id);
    }

    /**
     * Get effective 11-char YouTube ID.
     */
    public function getEffectiveYoutubeIdAttribute(): ?string
    {
        if (!empty($this->youtube_video_id)) {
            return $this->youtube_video_id;
        }
        return \App\Helpers\MediaHelper::extractYouTubeId($this->video_url);
    }

    /**
     * Check if this episode is sourced from direct storage (MP4/WebM).
     */
    public function isStorage(): bool
    {
        return !$this->isYouTube();
    }

    /**
     * Get high-quality thumbnail preview URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->isYouTube() && !empty($this->youtube_video_id)) {
            return \App\Helpers\MediaHelper::getYouTubeThumbnail($this->youtube_video_id);
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
