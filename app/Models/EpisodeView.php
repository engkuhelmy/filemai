<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeView extends Model
{
    use HasFactory;

    protected $table = 'episode_views';

    protected $fillable = [
        'drama_id',
        'episode_id',
        'user_id',
        'session_id',
        'ip_address',
        'device_type',
        'browser',
        'platform',
        'is_vip',
        'viewed_at',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'viewed_at' => 'datetime',
    ];

    /**
     * The drama associated with this view event.
     */
    public function drama(): BelongsTo
    {
        return $this->belongsTo(Drama::class, 'drama_id');
    }

    /**
     * The episode associated with this view event.
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'episode_id');
    }

    /**
     * The user who viewed the episode (if logged in).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Record a real view event.
     */
    public static function recordView(Drama $drama, Episode $episode, $request): self
    {
        $userAgent = $request->userAgent() ?? '';
        
        // Detect Device Type
        $deviceType = 'desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile|mobile)/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        // Detect Platform
        $platform = 'Unknown';
        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/windows nt/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        // Detect Browser
        $browser = 'Browser';
        if (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/edg/i', $userAgent)) {
            $browser = 'Edge';
        }

        $user = $request->user();
        $isVip = $user ? $user->isPremium() : false;

        $view = self::create([
            'drama_id' => $drama->id,
            'episode_id' => $episode->id,
            'user_id' => $user ? $user->id : null,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
            'is_vip' => $isVip,
            'viewed_at' => now(),
        ]);

        return $view;
    }
}
