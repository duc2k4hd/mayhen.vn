<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingVendor extends Model
{
    protected $table = 'wedding_journey_vendors';

    protected $fillable = [
        'wedding_project_id',
        'category',
        'name',
        'phone',
        'quote_vnd',
        'deposit_vnd',
        'status',
        'pros',
        'cons',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quote_vnd' => 'integer',
            'deposit_vnd' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class, 'wedding_project_id');
    }
}
