<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionMedia extends Model
{
    protected $fillable = [
        'url',
        'media_type',
    ];
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'media_type' => MediaType::class,
            'created_at' => 'datetime',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

