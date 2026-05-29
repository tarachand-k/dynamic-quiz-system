<?php

namespace App\Evaluators;

use App\Models\Answer;

class NumberInputEvaluator implements QuestionEvaluatorInterface
{
    public function evaluate(Answer $answer): EvaluationResult
    {
        $key = $answer->question->answerKey;
        $submitted = $answer->number_value;
        $correct = $key->correct_number_value;
        $tolerance = $key->number_tolerance ?? 0;

        $isCorrect = $submitted !== null
            && abs($submitted - $correct) <= $tolerance;

        return new EvaluationResult(
            isCorrect: $isCorrect,
            marksAwarded: $isCorrect ? (float)$answer->question->marks : 0.0,
        );
    }
}
