<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'creator_id',
        'approver_id',
        'action_type',
        'status',
        'amount',
        'reference_code',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'reconciliation_note',
        'review_note',
        'reconciled_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'reconciled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved' || $this->status === 'completed';
    }
}
