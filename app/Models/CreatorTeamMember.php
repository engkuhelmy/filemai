<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorTeamMember extends Model
{
    use HasFactory;

    protected $table = 'creator_team_members';

    protected $fillable = [
        'creator_id',
        'name',
        'email',
        'role',
        'permissions',
        'status',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
