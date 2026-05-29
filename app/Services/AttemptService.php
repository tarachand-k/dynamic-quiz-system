<?php

namespace App\Services;

use App\Evaluators\QuestionEvaluatorFactory;
use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;

class AttemptService
{
    public function submit(Quiz $quiz, int $userId, array $answers): Attempt
    {
        return DB::transaction(function () use ($quiz, $userId, $answers) {
            $attempt = Attempt::create([
                'quiz_id' => $quiz->id,
                'user_id' => $userId ?: null,
            ]);

            $quiz->loadMissing('questions');

            foreach ($quiz->questions as $question) {
                $data = $answers[$question->id] ?? [];

                $answer = Answer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'text_value' => $data['text_value'] ?? null,
                    'number_value' => isset($data['number_value']) && $data['number_value'] !== ''
                        ? (float)$data['number_value'] : null,
                ]);

                if (!empty($data['option_ids'])) {
                    $answer->selectedOptions()->sync($data['option_ids']);
                }
            }

            // Reload for evaluation
            $attempt->load([
                'answers.question.questionType',
                'answers.question.options',
                'answers.question.answerKey',
                'answers.selectedOptions',
            ]);

            foreach ($attempt->answers as $answer) {
                $slug = $answer->question->questionType->slug;
                $evaluator = QuestionEvaluatorFactory::make($slug);
                $result = $evaluator->evaluate($answer);

                $answer->update([
                    'is_correct' => $result->isCorrect,
                    'marks_awarded' => $result->marksAwarded,
                ]);
            }

            $attempt->update([
                'total_score' => $attempt->answers()->sum('marks_awarded'),
                'submitted_at' => now(),
            ]);

            return $attempt->fresh();
        });
    }
}
