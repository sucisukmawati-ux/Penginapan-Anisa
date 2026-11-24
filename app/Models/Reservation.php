<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'guest_name',
        'guest_phone',
        'check_in',
        'check_out',
        'nights',
        'duration_type',
        'start_at',
        'end_at',
        'total_amount',
        'status',
        'payment_status',
        'notes',
        'midtrans_order_id',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'nights' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getGuestDisplayNameAttribute(): string
    {
        return trim((string) ($this->guest_name ?: ($this->user?->name ?? 'Tamu')));
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function nights(): int
    {
        $storedNights = $this->nights ?? 0;

        if ((int) $storedNights > 0) {
            return (int) $storedNights;
        }

        if ($this->check_in && $this->check_out) {
            return max(1, (int) $this->check_in->diffInDays($this->check_out));
        }

        return 1;
    }
}
