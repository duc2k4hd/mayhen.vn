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
        'event_id',
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
            'event_id' => 'integer',
            'capacity' => 'integer',
            'pos_x' => 'integer',
            'pos_y' => 'integer',
            'display_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (WeddingTable $table) {
            if ($table->wasChanged('name')) {
                $table->guests()->update(['table_name' => $table->name]);
            }
        });

        static::deleting(function (WeddingTable $table) {
            $table->guests()->update([
                'table_id' => null,
                'table_name' => null,
            ]);
        });
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class, 'event_id');
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
