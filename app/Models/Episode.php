<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Episode extends Model
{
    use HasFactory;

    protected $table = 'episodes';

    protected $fillable = [
        'drama_id',
        'episode_number',
        'title',
        'description',
        'video_type',
        'video_source',
        'youtube_video_id',
        'video_url',
        'thumbnail_url',
        'duration',
        'views_count',
        'status',
        'scheduled_at',
        'is_free_preview',
        'is_coming_soon',
    ];

    protected $casts = [
        'episode_number' => 'integer',
        'duration' => 'integer',
        'views_count' => 'integer',
        'is_free_preview' => 'boolean',
        'is_coming_soon' => 'boolean',
        'scheduled_at' => 'datetime',
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
     * Get high-quality thumbnail preview URL (custom thumbnail, YouTube thumbnail, or drama poster fallback).
     */
    public function getThumbnailUrlAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        if ($this->isYouTube() && !empty($this->effective_youtube_id)) {
            return \App\Helpers\MediaHelper::getYouTubeThumbnail($this->effective_youtube_id);
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

    /**
     * Check if episode is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if episode is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if episode is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if episode is released (published or scheduled and past scheduled_at).
     */
    public function isReleased(): bool
    {
        if ($this->status === 'published') {
            return true;
        }
        if ($this->status === 'scheduled' && $this->scheduled_at) {
            return Carbon::parse($this->scheduled_at)->isPast();
        }
        return false;
    }
}
