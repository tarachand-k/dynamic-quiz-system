<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Answer extends Model
{
    protected $fillable = [
        'attempt_id',
        'question_id',
        'number_value',
        'text_value',
        'is_correct',
        'marks_awarded',
    ];
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'marks_awarded' => 'float',
            'number_value' => 'float',
            'created_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOptions(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'answer_options');
    }
}
