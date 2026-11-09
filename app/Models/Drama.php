<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Drama extends Model
{
    use HasFactory;

    protected $table = 'dramas';

    protected $fillable = [
        'creator_id',
        'title',
        'slug',
        'synopsis',
        'poster_url',
        'banner_url',
        'genre',
        'type',
        'status',
        'views_count',
        'featured',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'views_count' => 'integer',
    ];

    /**
     * Relationship: The creator/filmmaker who owns this drama.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relationship: Ordered episodes of this drama series.
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'drama_id')->orderBy('episode_number', 'asc');
    }

    /**
     * Scope: Only published dramas visible to viewers.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope: Featured dramas on homepage hero.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Check if drama name (title) and poster can still be edited (within 24 hours of creation).
     * Super Admins can always edit.
     */
    public function canEditIdentity(?User $user = null): bool
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
     * Check if identity (title & poster) is locked after 24 hours.
     */
    public function isIdentityLocked(?User $user = null): bool
    {
        return !$this->canEditIdentity($user);
    }

    /**
     * Human-readable remaining time to edit title & poster (e.g. '23 jam 15 minit lagi').
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
