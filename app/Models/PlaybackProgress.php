<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaybackProgress extends Model
{
    use HasFactory;

    protected $table = 'playback_progress';

    protected $fillable = [
        'user_id',
        'session_id',
        'drama_id',
        'episode_id',
        'progress_seconds',
        'duration_seconds',
        'completed',
        'last_watched_at',
    ];

    protected $casts = [
        'progress_seconds' => 'integer',
        'duration_seconds' => 'integer',
        'completed' => 'boolean',
        'last_watched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function drama(): BelongsTo
    {
        return $this->belongsTo(Drama::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Record or update playback progress.
     */
    public static function recordProgress(
        int $dramaId,
        int $episodeId,
        int $progressSeconds,
        int $durationSeconds = 60,
        ?int $userId = null,
        ?string $sessionId = null
    ): self {
        $completed = ($durationSeconds > 0 && $progressSeconds >= ($durationSeconds - 5));

        $attributes = [
            'drama_id' => $dramaId,
            'episode_id' => $episodeId,
        ];

        if ($userId) {
            $attributes['user_id'] = $userId;
        } elseif ($sessionId) {
            $attributes['session_id'] = $sessionId;
        }

        $record = self::firstOrNew($attributes);
        $record->progress_seconds = $progressSeconds;
        $record->duration_seconds = max($durationSeconds, 1);
        $record->completed = $completed;
        $record->last_watched_at = now();
        $record->save();

        return $record;
    }

    /**
     * Get last saved progress for an episode.
     */
    public static function getProgress(
        int $episodeId,
        ?int $userId = null,
        ?string $sessionId = null
    ): ?self {
        $query = self::where('episode_id', $episodeId);

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
            return null;
        }

        return $query->orderBy('last_watched_at', 'desc')->first();
    }
}

