<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'base_price',
        'capacity',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'capacity' => 'integer',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class);
    }

    public function getCoverUrlAttribute(): string
    {
        $raw = $this->image ?? $this->image_url ?? null;

        if ($raw) {
            return Str::startsWith($raw, ['http://', 'https://'])
                ? $raw
                : asset('storage/' . ltrim($raw, '/'));
        }

        $typeName = strtolower($this->name ?? '');

        return match (true) {
            str_contains($typeName, 'suite') || str_contains($typeName, 'executive')
                => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
            str_contains($typeName, 'superior')
                => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80',
            default
                => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80',
        };
    }

    public function getHasLocalImageAttribute(): bool
    {
        return ! empty($this->image) && ! Str::startsWith($this->image, ['http://', 'https://']);
    }
}