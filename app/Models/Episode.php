<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Services\YouTubeService;

class Episode extends Model
{
    use HasFactory;

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
        'views_count' => 'integer',
        'is_free_preview' => 'boolean',
        'is_coming_soon' => 'boolean',
        'scheduled_at' => 'datetime',
    ];

    /**
     * Relationship: Episode belongs to a Drama.
     */
    public function drama(): BelongsTo
    {
        return $this->belongsTo(Drama::class);
    }

    /**
     * Helper to retrieve effective YouTube Video ID.
     */
    public function getEffectiveYoutubeIdAttribute(): ?string
    {
        if (!empty($this->youtube_video_id)) {
            return $this->youtube_video_id;
        }
        if (!empty($this->video_url)) {
            return \App\Helpers\MediaHelper::extractYouTubeId($this->video_url);
        }
        return null;
    }

    /**
     * Detect video source type (youtube or direct).
     */
    public function getSourceTypeAttribute(): string
    {
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
     * Check if this episode is stored locally or on CDN.
     */
    public function isStorage(): bool
    {
        if ($this->isComingSoon()) {
            return false;
        }

        return !$this->isYouTube();
    }

    /**
     * Get high-quality thumbnail preview URL (custom thumbnail, YouTube thumbnail, or drama poster fallback).
     */
    public function getThumbnailUrlAttribute($value = null): string
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
     * Format duration into MM:SS.
     */
    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration ?? 0;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
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
