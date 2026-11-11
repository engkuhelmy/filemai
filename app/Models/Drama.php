<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Drama extends Model
{
    use HasFactory;

    protected $table = 'dramas';

    protected $fillable = [
        'creator_id',
        'title',
        'slug',
        'synopsis',
        'meta_title',
        'meta_description',
        'keywords',
        'creator_bio',
        'poster_url',
        'banner_url',
        'trailer_url',
        'genre',
        'genres',
        'type',
        'release_status',
        'cast',
        'age_rating',
        'content_warnings',
        'announcement',
        'season_price',
        'is_bundle_enabled',
        'bundle_discount_note',
        'social_hashtag',
        'social_links',
        'accent_color',
        'status',
        'views_count',
        'featured',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'views_count' => 'integer',
        'is_bundle_enabled' => 'boolean',
        'season_price' => 'decimal:2',
        'genres' => 'array',
        'cast' => 'array',
        'content_warnings' => 'array',
        'social_links' => 'array',
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
     * Effective Accent Color with fallback to Netflix brand red.
     */
    public function getEffectiveAccentColorAttribute(): string
    {
        return !empty($this->accent_color) ? $this->accent_color : '#E50914';
    }

    /**
     * Normalized list of genres.
     */
    public function getGenresListAttribute(): array
    {
        if (is_array($this->genres) && !empty($this->genres)) {
            return $this->genres;
        }
        if (!empty($this->genre)) {
            return array_map('trim', explode(',', $this->genre));
        }
        return ['Romance'];
    }

    /**
     * Normalized list of cast & crew members.
     */
    public function getCastListAttribute(): array
    {
        if (is_array($this->cast)) {
            return $this->cast;
        }
        if (is_string($this->cast) && !empty($this->cast)) {
            $decoded = json_decode($this->cast, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            // Parse line-by-line: Actor Name - Character Name
            $lines = explode("\n", str_replace("\r", "", $this->cast));
            $list = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                $parts = explode('-', $line, 2);
                $list[] = [
                    'actor' => trim($parts[0]),
                    'character' => isset($parts[1]) ? trim($parts[1]) : '',
                ];
            }
            return $list;
        }
        return [];
    }

    /**
     * Normalized content warnings list.
     */
    public function getContentWarningsListAttribute(): array
    {
        if (is_array($this->content_warnings)) {
            return $this->content_warnings;
        }
        if (is_string($this->content_warnings) && !empty($this->content_warnings)) {
            $decoded = json_decode($this->content_warnings, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            return array_map('trim', explode(',', $this->content_warnings));
        }
        return [];
    }

    /**
     * Normalized social links.
     */
    public function getSocialLinksListAttribute(): array
    {
        if (is_array($this->social_links)) {
            return $this->social_links;
        }
        if (is_string($this->social_links) && !empty($this->social_links)) {
            $decoded = json_decode($this->social_links, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Human-readable Release Status Badge data.
     */
    public function getReleaseStatusBadgeAttribute(): array
    {
        return match($this->release_status) {
            'completed' => [
                'label' => 'Tamat (Completed)',
                'badge' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                'dot' => 'bg-emerald-400',
            ],
            'hiatus' => [
                'label' => 'Berehat (Hiatus)',
                'badge' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                'dot' => 'bg-amber-400',
            ],
            'upcoming' => [
                'label' => 'Akan Datang (Upcoming)',
                'badge' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
                'dot' => 'bg-cyan-400',
            ],
            default => [
                'label' => 'Sedang Ditayangkan (Ongoing)',
                'badge' => 'bg-red-500/20 text-red-400 border-red-500/30',
                'dot' => 'bg-red-400',
            ],
        };
    }

    /**
     * Fallback SEO Meta Title.
     */
    public function getEffectiveMetaTitleAttribute(): string
    {
        if (!empty($this->meta_title)) {
            return $this->meta_title;
        }
        return "{$this->title} - Tonton Siri Drama Menegak | Filemai.my";
    }

    /**
     * Fallback SEO Meta Description.
     */
    public function getEffectiveMetaDescriptionAttribute(): string
    {
        if (!empty($this->meta_description)) {
            return $this->meta_description;
        }
        return Str::limit(strip_tags($this->synopsis ?? ''), 160, '...');
    }
}
