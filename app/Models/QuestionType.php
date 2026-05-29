<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionType extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'has_options' => 'boolean',
            'allows_multiple_correct' => 'boolean',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
