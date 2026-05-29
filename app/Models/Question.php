<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
    protected $fillable = [
        'question_type_id',
        'body',
        'marks',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'marks' => 'float',
            'order_index' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('order_index');
    }

    public function answerKey(): HasOne
    {
        return $this->hasOne(QuestionAnswerKey::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(QuestionMedia::class);
    }

    public function getImageMediaAttribute(): ?QuestionMedia
    {
        return $this->media->firstWhere('media_type', MediaType::Image);
    }

    public function getVideoMediaAttribute(): ?QuestionMedia
    {
        return $this->media->firstWhere('media_type', MediaType::Video);
    }
}
