<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiKnowledgeVersion extends Model
{
    protected $table = 'ai_knowledge_versions';

    protected $fillable = [
        'knowledge_entry_id',
        'version',
        'category',
        'title',
        'keywords',
        'answer',
        'template_slug',
        'doc_url',
        'trust_score',
        'evidence_refs',
        'change_reason',
        'changed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'trust_score' => 'integer',
            'evidence_refs' => 'array',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(AiKnowledgeEntry::class, 'knowledge_entry_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
