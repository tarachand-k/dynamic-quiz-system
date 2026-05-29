<?php

namespace App\Models;

use App\Enums\MatchStrategy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionAnswerKey extends Model
{
    protected $fillable = [
        'correct_number_value',
        'correct_text_value',
        'number_tolerance',
        'match_strategy',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'correct_number_value' => 'float',
            'number_tolerance' => 'float',
            'match_strategy' => MatchStrategy::class,
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
