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
        'is_coming_soon',
    ];

    protected $casts = [
        'episode_number' => 'integer',
        'duration' => 'integer',
        'views_count' => 'integer',
        'is_free_preview' => 'boolean',
        'is_coming_soon' => 'boolean',
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
     * Check if this episode is coming soon (no movie/video uploaded yet).
     */
    public function isComingSoon(): bool
    {
        if ($this->is_coming_soon) {
            return true;
        }

        return empty($this->youtube_video_id) && empty($this->video_url);
    }

    /**
     * Check if this episode is sourced from YouTube.
     */
    public function isYouTube(): bool
    {
        if ($this->isComingSoon()) {
            return false;
        }

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
        if ($this->isComingSoon()) {
            return false;
        }

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

    /**
     * Check if episode title/name can still be edited (within 24 hours of creation).
     * Super Admins can always edit.
     */
    public function canEditTitle(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (!$this->created_at) {
            return true;
        }

        return $this->created_at->diffInHours(now()) < 24;
    }

    /**
     * Check if title is locked after 24 hours.
     */
    public function isTitleLocked(?User $user = null): bool
    {
        return !$this->canEditTitle($user);
    }

    /**
     * Human-readable remaining time to edit title (e.g. '23 jam 15 minit lagi').
     */
    public function remainingEditTime(): ?string
    {
        if (!$this->created_at) {
            return null;
        }

        $deadline = $this->created_at->copy()->addHours(24);
        if (now()->greaterThanOrEqualTo($deadline)) {
            return null;
        }

        $diffHours = (int) now()->diffInHours($deadline, false);
        $diffMinutes = (int) (now()->diffInMinutes($deadline, false) % 60);

        if ($diffHours > 0) {
            return "{$diffHours} jam {$diffMinutes} minit lagi";
        }

        return "{$diffMinutes} minit lagi";
    }
}
