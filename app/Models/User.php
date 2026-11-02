<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'subscription_status',
        'subscription_expires_at',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'subscription_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user has an active premium subscription.
     * Admins automatically receive VIP ad-free privileges.
     * Premium users must have subscription_status === 'premium' AND subscription_expires_at > now().
     */
    public function isPremium(): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->subscription_status !== 'premium') {
            return false;
        }

        if ($this->subscription_expires_at === null) {
            return true;
        }

        return Carbon::parse($this->subscription_expires_at)->isFuture();
    }

    /**
     * Check if user is a creator or admin.
     */
    public function isCreator(): bool
    {
        return in_array($this->role, ['creator', 'admin']);
    }

    /**
     * Check if user is a super admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Dramas created by this user.
     */
    public function dramas(): HasMany
    {
        return $this->hasMany(Drama::class, 'creator_id');
    }
}
