<?php

namespace App\Evaluators;

use App\Models\Answer;

class MultipleChoiceEvaluator implements QuestionEvaluatorInterface
{
    public function evaluate(Answer $answer): EvaluationResult
    {
        $allOptions = $answer->question->options;
        $correctIds = $allOptions->where('is_correct', true)->pluck('id')->sort()->values();
        $selectedIds = $answer->selectedOptions->pluck('id')->sort()->values();

        $isCorrect = $correctIds->count() > 0
            && $correctIds->toArray() === $selectedIds->toArray();

        return new EvaluationResult(
            isCorrect: $isCorrect,
            marksAwarded: $isCorrect ? (float)$answer->question->marks : 0.0,
        );
    }
}
