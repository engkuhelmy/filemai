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
}
