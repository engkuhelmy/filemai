<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class CreatorWallet extends Model
{
    protected $table = 'creator_wallets';

    protected $fillable = [
        'user_id',
        'total_earned',
        'total_withdrawn',
        'current_balance',
        'total_blocks',
    ];

    protected function casts(): array
    {
        return [
            'total_earned' => 'decimal:6',
            'total_withdrawn' => 'decimal:6',
            'current_balance' => 'decimal:6',
            'total_blocks' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function earningLogs(): HasMany
    {
        return $this->hasMany(CreatorEarningLog::class, 'creator_id', 'user_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(CreatorPayout::class, 'creator_id', 'user_id');
    }

    /**
     * Get or create wallet instance for a user.
     */
    public static function forUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'total_earned' => 0.000000,
                'total_withdrawn' => 0.000000,
                'current_balance' => 0.000000,
                'total_blocks' => 0,
            ]
        );
    }

    /**
     * Record video watch royalty earnings for creator based on watched duration.
     * Rule: Setiap blok 1 minit (60 saat) penuh = 1 blok.
     * Cth: 1 minit 59 saat (119 saat) -> floor(119 / 60) = 1 blok (RM 0.0001).
     *
     * @param Drama $drama
     * @param Episode $episode
     * @param int|null $durationSeconds
     * @return array
     */
    public static function recordViewEarnings(Drama $drama, Episode $episode, ?int $durationSeconds = null): array
    {
        $creatorId = $drama->creator_id;
        if (!$creatorId) {
            return ['credited' => false, 'reason' => 'No creator assigned'];
        }

        // Get duration: passed in seconds or fallback to episode duration
        $duration = $durationSeconds !== null && $durationSeconds > 0 
            ? $durationSeconds 
            : ($episode->duration > 0 ? (int)$episode->duration : 60);

        // System settings for block duration and rate
        $blockSeconds = (int) SystemSetting::get('block_duration_seconds', 60);
        if ($blockSeconds <= 0) {
            $blockSeconds = 60;
        }

        // Calculate full blocks (floor)
        $blocks = (int) floor($duration / $blockSeconds);

        $rate = (float) SystemSetting::get('creator_earnings_rate_per_block', 0.000100);
        $amount = (float) ($blocks * $rate);

        if ($blocks > 0 && $amount > 0) {
            DB::transaction(function () use ($creatorId, $drama, $episode, $duration, $blocks, $rate, $amount) {
                $wallet = self::forUser($creatorId);

                $wallet->total_earned = (float)$wallet->total_earned + $amount;
                $wallet->current_balance = (float)$wallet->current_balance + $amount;
                $wallet->total_blocks = (int)$wallet->total_blocks + $blocks;
                $wallet->save();

                CreatorEarningLog::create([
                    'creator_id' => $creatorId,
                    'drama_id' => $drama->id,
                    'episode_id' => $episode->id,
                    'duration_seconds' => $duration,
                    'blocks_count' => $blocks,
                    'rate_per_block' => $rate,
                    'amount' => $amount,
                    'created_at' => now(),
                ]);
            });

            return [
                'credited' => true,
                'creator_id' => $creatorId,
                'duration' => $duration,
                'blocks' => $blocks,
                'rate' => $rate,
                'amount' => $amount,
            ];
        }

        return [
            'credited' => false,
            'reason' => 'Duration under 1 block',
            'duration' => $duration,
            'blocks' => $blocks,
        ];
    }

    /**
     * Deduct balance when a payout is requested or approved.
     */
    public function deductPayout(float $amount): bool
    {
        if ($this->current_balance < $amount) {
            return false;
        }

        $this->current_balance = (float)$this->current_balance - $amount;
        $this->total_withdrawn = (float)$this->total_withdrawn + $amount;
        return $this->save();
    }

    /**
     * Formatted string helpers
     */
    public function getFormattedEarnedAttribute(): string
    {
        return 'RM ' . number_format($this->total_earned, 4);
    }

    public function getFormattedWithdrawnAttribute(): string
    {
        return 'RM ' . number_format($this->total_withdrawn, 4);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'RM ' . number_format($this->current_balance, 4);
    }
}

