<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeddingTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'name',
        'shape',
        'capacity',
        'pos_x',
        'pos_y',
        'zone',
        'notes',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'pos_x' => 'integer',
            'pos_y' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class, 'table_id');
    }

    public function getAssignedAttendeesCountAttribute(): int
    {
        return (int) $this->guests->sum(function (Guest $g) {
            return $g->actualAttendeesCount();
        });
    }

    public function isFull(): bool
    {
        return $this->assigned_attendees_count >= $this->capacity;
    }

    public function isOverCapacity(): bool
    {
        return $this->assigned_attendees_count > $this->capacity;
    }

    public function getRemainingSeatsAttribute(): int
    {
        return max(0, $this->capacity - $this->assigned_attendees_count);
    }

    public function getShapeLabelAttribute(): string
    {
        return match ($this->shape) {
            'long' => 'Bàn Dài',
            'square' => 'Bàn Vuông',
            default => 'Bàn Tròn',
        };
    }
}
