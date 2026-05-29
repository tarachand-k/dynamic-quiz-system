<?php

namespace App\Evaluators;

use App\Models\Answer;

interface QuestionEvaluatorInterface
{
    public function evaluate(Answer $answer): EvaluationResult;
}
