<?php

namespace App\Evaluators;

readonly class EvaluationResult
{
    public function __construct(
        public bool  $isCorrect,
        public float $marksAwarded,
    )
    {
    }
}
