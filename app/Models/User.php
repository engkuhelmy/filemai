<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'headline',
        'bio',
        'avatar_url',
        'cover_photo_url',
        'specialties',
        'social_links',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'bank_ic_ssm',
        'is_bank_verified',
        'subscription_status',
        'subscription_expires_at',
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
            'specialties' => 'array',
            'social_links' => 'array',
            'is_bank_verified' => 'boolean',
        ];
    }

    /**
     * Check if user has an active premium subscription.
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

    /**
     * Creator Wallet (Dompet Pendapatan).
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(CreatorWallet::class, 'user_id');
    }

    /**
     * Get or create wallet helper.
     */
    public function getOrCreateWallet(): CreatorWallet
    {
        return $this->wallet ?: $this->wallet()->create([
            'total_earned' => 0,
            'total_withdrawn' => 0,
            'current_balance' => 0,
            'pending_balance' => 0,
            'total_blocks' => 0,
        ]);
    }

    /**
     * Creator Payouts.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(CreatorPayout::class, 'creator_id')->orderBy('created_at', 'desc');
    }

    /**
     * Creator Earning Logs.
     */
    public function earningLogs(): HasMany
    {
        return $this->hasMany(CreatorEarningLog::class, 'creator_id')->orderBy('created_at', 'desc');
    }

    /**
     * Fan Tips received.
     */
    public function fanTips(): HasMany
    {
        return $this->hasMany(FanTip::class, 'creator_id')->orderBy('created_at', 'desc');
    }

    /**
     * Promo codes created by this creator.
     */
    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class, 'creator_id')->orderBy('created_at', 'desc');
    }

    /**
     * Creator notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(CreatorNotification::class, 'user_id')->orderBy('created_at', 'desc');
    }

    /**
     * Team members.
     */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(CreatorTeamMember::class, 'creator_id')->orderBy('created_at', 'desc');
    }

    /**
     * Support & Takedown tickets.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(CreatorSupportTicket::class, 'creator_id')->orderBy('created_at', 'desc');
    }

    /**
     * Ciri 12: Status & Creator Level calculation.
     */
    public function getCreatorLevelAttribute(): array
    {
        $totalViews = $this->dramas()->sum('views_count');

        if ($totalViews >= 100000) {
            return [
                'level' => 4,
                'name' => 'Top Rated Partner',
                'badge' => 'bg-cyan-500/20 text-cyan-300 border-cyan-400/40',
                'dot' => 'bg-cyan-400',
                'icon' => 'flame',
                'views' => $totalViews,
                'next_threshold' => null,
                'progress' => 100,
            ];
        } elseif ($totalViews >= 25000) {
            return [
                'level' => 3,
                'name' => 'Gold Filmmaker',
                'badge' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                'dot' => 'bg-amber-400',
                'icon' => 'crown',
                'views' => $totalViews,
                'next_threshold' => 100000,
                'progress' => min(100, round(($totalViews / 100000) * 100)),
            ];
        } elseif ($totalViews >= 5000) {
            return [
                'level' => 2,
                'name' => 'Silver Creator',
                'badge' => 'bg-slate-300/20 text-slate-200 border-slate-300/40',
                'dot' => 'bg-slate-300',
                'icon' => 'award',
                'views' => $totalViews,
                'next_threshold' => 25000,
                'progress' => min(100, round(($totalViews / 25000) * 100)),
            ];
        } else {
            return [
                'level' => 1,
                'name' => 'Rising Star',
                'badge' => 'bg-red-500/20 text-red-300 border-red-500/40',
                'dot' => 'bg-red-400',
                'icon' => 'sparkles',
                'views' => $totalViews,
                'next_threshold' => 5000,
                'progress' => min(100, round(($totalViews / 5000) * 100)),
            ];
        }
    }
}
