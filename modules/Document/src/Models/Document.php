<?php

namespace Modules\Document\src\Models;

use Dom\Document as DomDocument;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Lessons\src\Models\Lesson;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'name',
        'url',
        'size'
    ];

    protected $attributes = [
        'size' => 0
    ];

    public function document(){
        return $this->hasMany(DomDocument::class,'document_id','id');
    }
}