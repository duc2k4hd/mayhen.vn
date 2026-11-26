<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaziRelationshipReport extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'person_a_name',
        'person_b_name',
        'report_json',
    ];

    protected function casts(): array
    {
        return [
            'report_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
