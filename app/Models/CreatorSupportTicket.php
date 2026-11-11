<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorSupportTicket extends Model
{
    use HasFactory;

    protected $table = 'creator_support_tickets';

    protected $fillable = [
        'creator_id',
        'type',
        'subject',
        'description',
        'drama_id',
        'proof_url',
        'status',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function drama(): BelongsTo
    {
        return $this->belongsTo(Drama::class, 'drama_id');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'resolved', 'closed' => [
                'label' => 'Selesai',
                'badge' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
            ],
            'in_progress' => [
                'label' => 'Sedang Disemak',
                'badge' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
            ],
            default => [
                'label' => 'Tiket Baharu',
                'badge' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            ],
        };
    }
}

