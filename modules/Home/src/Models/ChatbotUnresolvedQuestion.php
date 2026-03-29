<?php

namespace Modules\Home\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Students\src\Models\Student;

class ChatbotUnresolvedQuestion extends Model
{
    protected $table = 'chatbot_unresolved_questions';

    protected $fillable = [
        'message',
        'normalized_message',
        'resolved_message',
        'intent_tags',
        'source',
        'fallback_reason',
        'student_id',
        'knowledge_id',
        'status',
        'hit_count',
        'last_asked_at',
        'resolved_at',
        'locale',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'intent_tags' => 'array',
        'student_id' => 'integer',
        'knowledge_id' => 'integer',
        'hit_count' => 'integer',
        'last_asked_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function knowledge(): BelongsTo
    {
        return $this->belongsTo(ChatbotKnowledge::class, 'knowledge_id');
    }
}
