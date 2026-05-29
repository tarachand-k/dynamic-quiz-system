<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Option extends Model
{
    protected $fillable = [
        'body',
        'media_url',
        'is_correct',
        'order_index',
    ];
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'order_index' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
