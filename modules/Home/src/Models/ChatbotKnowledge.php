<?php

namespace Modules\Home\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotKnowledge extends Model
{
    protected $table = 'chatbot_knowledge';

    protected $fillable = [
        'question',
        'keywords',
        'answer',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function unresolvedQuestions(): HasMany
    {
        return $this->hasMany(ChatbotUnresolvedQuestion::class, 'knowledge_id');
    }

    public function keywordList(): array
    {
        return collect(preg_split('/[\r\n,;]+/u', (string) $this->keywords) ?: [])
            ->map(fn ($keyword) => trim((string) $keyword))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
