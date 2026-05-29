<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'total_score',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'float',
            'submitted_at' => 'datetime',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
