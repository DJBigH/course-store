<?php

namespace Modules\Announcements\src\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\src\Models\User;
use Modules\Students\src\Models\Student;

class Announcement extends Model
{
    use SoftDeletes;

    protected $table = 'announcements';

    protected $fillable = [
        'title', 'title_en', 'title_ko', 'title_ja', 'title_zh',
        'message', 'message_en', 'message_ko', 'message_ja', 'message_zh',
        'content', 'content_en', 'content_ko', 'content_ja', 'content_zh',
        'target_type',
        'action_url', 'action_label', 'action_label_en', 'action_label_ko', 'action_label_ja', 'action_label_zh',
        'send_email',
        'created_by',
        'sent_at'
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function recipients()
    {
        return $this->belongsToMany(Student::class, 'announcement_user', 'announcement_id', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads()
    {
        return $this->hasMany(AnnouncementRead::class, 'announcement_id', 'id');
    }

    public function getTitleLocaleAttribute()
    {
        return $this->getLocalizedAttribute('title');
    }

    public function getMessageLocaleAttribute()
    {
        return $this->getLocalizedAttribute('message');
    }

    public function getContentLocaleAttribute()
    {
        return $this->getLocalizedAttribute('content');
    }

    public function getActionLabelLocaleAttribute()
    {
        return $this->getLocalizedAttribute('action_label');
    }

    private function getLocalizedAttribute($attribute)
    {
        $locale = app()->getLocale();
        $localizedField = $attribute . '_' . $locale;
        
        if ($locale !== 'vi' && !empty($this->{$localizedField})) {
            return $this->{$localizedField};
        }

        return $this->{$attribute};
    }
}
