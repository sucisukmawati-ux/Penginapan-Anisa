<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Room extends Model
{
    use HasFactory;

    public const TYPE_FAN = 'fan';

    public const TYPE_AC = 'ac';

    public const ROOM_TYPES = [self::TYPE_FAN, self::TYPE_AC];

    public const RATES = [
        self::TYPE_FAN => [
            'three_hours' => 100000,
            'half_day' => 150000,
            'overnight' => 250000,
        ],
        self::TYPE_AC => [
            'three_hours' => 150000,
            'half_day' => 200000,
            'overnight' => 350000,
        ],
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'room_type',
        'price_per_night',
        'capacity',
        'status',
        'image_url',
        'is_featured',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function formattedPrice(): string
    {
        return 'Rp '.number_format((float) ($this->price_per_night ?? 0), 0, ',', '.');
    }

    public function imageUrl(): ?string
    {
        $image = $this->image_url;

        if (! is_string($image) || $image === '') {
            return null;
        }

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        return str_starts_with($image, 'images/')
            ? asset($image)
            : Storage::disk('public')->url($image);
    }

    public function rateFor(string $durationType): int
    {
        $roomType = in_array($this->room_type, self::ROOM_TYPES, true) ? $this->room_type : self::TYPE_FAN;
        $normalizedDuration = in_array($durationType, array_keys(self::RATES[$roomType]), true) ? $durationType : 'overnight';

        return self::RATES[$roomType][$normalizedDuration];
    }
}
