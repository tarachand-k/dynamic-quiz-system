<?php

namespace App\Evaluators;

use App\Enums\MatchStrategy;
use App\Models\Answer;

class TextInputEvaluator implements QuestionEvaluatorInterface
{
    public function evaluate(Answer $answer): EvaluationResult
    {
        $key = $answer->question->answerKey;
        $submitted = trim((string)$answer->text_value);
        $correct = trim((string)$key->correct_text_value);

        $isCorrect = match ($key->match_strategy) {
            MatchStrategy::Exact => $submitted === $correct,
            MatchStrategy::CaseInsensitive => strcasecmp($submitted, $correct) === 0,
            MatchStrategy::Contains => str_contains(
                strtolower($submitted),
                strtolower($correct)
            ),
        };

        return new EvaluationResult(
            isCorrect: $isCorrect,
            marksAwarded: $isCorrect ? (float)$answer->question->marks : 0.0,
        );
    }
}
