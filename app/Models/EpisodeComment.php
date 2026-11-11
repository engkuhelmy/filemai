<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeComment extends Model
{
    use HasFactory;

    protected $table = 'episode_comments';

    protected $fillable = [
        'drama_id',
        'episode_id',
        'user_id',
        'author_name',
        'content',
        'is_pinned',
        'creator_reply',
        'likes_count',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'likes_count' => 'integer',
    ];

    public function drama(): BelongsTo
    {
        return $this->belongsTo(Drama::class, 'drama_id');
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'episode_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
