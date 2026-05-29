<?php

namespace App\Evaluators;

use App\Models\Answer;

class ChoiceEvaluator implements QuestionEvaluatorInterface
{
    public function evaluate(Answer $answer): EvaluationResult
    {
        $selected = $answer->selectedOptions;

        // Must select exactly one option and it must be correct
        $isCorrect = $selected->count() === 1
            && $selected->first()?->is_correct === true;

        return new EvaluationResult(
            isCorrect: $isCorrect,
            marksAwarded: $isCorrect ? (float)$answer->question->marks : 0.0,
        );
    }
}
