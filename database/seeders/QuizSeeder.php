<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuestionType;
use App\Models\Attempt;
use App\Models\Answer;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $types = QuestionType::all()->keyBy('slug');

        // Quiz 1: General Knowledge
        $quiz1 = Quiz::create([
            'user_id' => $user->id,
            'title' => 'General Knowledge',
            'description' => 'A mix of question types covering basic general knowledge.',
        ]);

        $q1 = $quiz1->questions()->create([
            'question_type_id' => $types['binary']->id,
            'body' => '<p>Is the Earth the third planet from the Sun?</p>',
            'marks' => 1,
            'order_index' => 1,
        ]);
        $q1->options()->createMany([
            ['body' => 'Yes', 'is_correct' => true, 'order_index' => 1],
            ['body' => 'No', 'is_correct' => false, 'order_index' => 2],
        ]);

        $q2 = $quiz1->questions()->create([
            'question_type_id' => $types['single_choice']->id,
            'body' => '<p>Which country has the largest population?</p>',
            'marks' => 1,
            'order_index' => 2,
        ]);
        $q2->options()->createMany([
            ['body' => 'USA', 'is_correct' => false, 'order_index' => 1],
            ['body' => 'India', 'is_correct' => true, 'order_index' => 2],
            ['body' => 'China', 'is_correct' => false, 'order_index' => 3],
            ['body' => 'Russia', 'is_correct' => false, 'order_index' => 4],
        ]);

        $q3 = $quiz1->questions()->create([
            'question_type_id' => $types['multiple_choice']->id,
            'body' => '<p>Which of the following are programming languages?</p>',
            'marks' => 2,
            'order_index' => 3,
        ]);
        $q3->options()->createMany([
            ['body' => 'Python', 'is_correct' => true, 'order_index' => 1],
            ['body' => 'Cobra', 'is_correct' => false, 'order_index' => 2],
            ['body' => 'Rust', 'is_correct' => true, 'order_index' => 3],
            ['body' => 'Falcon', 'is_correct' => false, 'order_index' => 4],
        ]);

        $q4 = $quiz1->questions()->create([
            'question_type_id' => $types['number_input']->id,
            'body' => '<p>How many planets are in our solar system?</p>',
            'marks' => 1,
            'order_index' => 4,
        ]);
        $q4->answerKey()->create([
            'correct_number_value' => 8,
            'number_tolerance' => 0,
            'match_strategy' => 'exact',
        ]);

        $q5 = $quiz1->questions()->create([
            'question_type_id' => $types['text_input']->id,
            'body' => '<p>What is the chemical symbol for water?</p>',
            'marks' => 1,
            'order_index' => 5,
        ]);
        $q5->answerKey()->create([
            'correct_text_value' => 'H2O',
            'match_strategy' => 'case_insensitive',
        ]);

        // Seed 3 attempts for quiz 1
        $this->seedAttempt($quiz1, $user, [
            $q1->id => ['option' => $q1->options->where('is_correct', true)->first()->id],
            $q2->id => ['option' => $q2->options->where('is_correct', true)->first()->id],
            $q3->id => ['options' => $q3->options->where('is_correct', true)->pluck('id')->toArray()],
            $q4->id => ['number' => 8],
            $q5->id => ['text' => 'H2O'],
        ], allCorrect: true);

        $this->seedAttempt($quiz1, $user, [
            $q1->id => ['option' => $q1->options->where('is_correct', true)->first()->id],
            $q2->id => ['option' => $q2->options->where('is_correct', false)->first()->id],
            $q3->id => ['options' => [$q3->options->where('is_correct', true)->first()->id]],
            $q4->id => ['number' => 7],
            $q5->id => ['text' => 'H2O'],
        ], allCorrect: false);

        $this->seedAttempt($quiz1, $user, [
            $q1->id => ['option' => $q1->options->where('is_correct', false)->first()->id],
            $q2->id => ['option' => $q2->options->where('is_correct', false)->first()->id],
            $q3->id => ['options' => [$q3->options->where('is_correct', false)->first()->id]],
            $q4->id => ['number' => 5],
            $q5->id => ['text' => 'H20'],
        ], allCorrect: false);

        // Quiz 2: Laravel Basics
        $quiz2 = Quiz::create([
            'user_id' => $user->id,
            'title' => 'Laravel Basics',
            'description' => 'Quick quiz on core Laravel concepts.',
        ]);

        $lq1 = $quiz2->questions()->create([
            'question_type_id' => $types['binary']->id,
            'body' => '<p>Is Laravel a PHP framework?</p>',
            'marks' => 1,
            'order_index' => 1,
        ]);
        $lq1->options()->createMany([
            ['body' => 'Yes', 'is_correct' => true, 'order_index' => 1],
            ['body' => 'No', 'is_correct' => false, 'order_index' => 2],
        ]);

        $lq2 = $quiz2->questions()->create([
            'question_type_id' => $types['single_choice']->id,
            'body' => '<p>Which command creates a new Laravel controller?</p>',
            'marks' => 1,
            'order_index' => 2,
        ]);
        $lq2->options()->createMany([
            ['body' => 'php artisan make:controller', 'is_correct' => true, 'order_index' => 1],
            ['body' => 'php artisan create:controller', 'is_correct' => false, 'order_index' => 2],
            ['body' => 'php artisan controller:new', 'is_correct' => false, 'order_index' => 3],
            ['body' => 'php artisan generate:controller', 'is_correct' => false, 'order_index' => 4],
        ]);

        $lq3 = $quiz2->questions()->create([
            'question_type_id' => $types['multiple_choice']->id,
            'body' => '<p>Which of these are valid Eloquent relationship methods?</p>',
            'marks' => 2,
            'order_index' => 3,
        ]);
        $lq3->options()->createMany([
            ['body' => 'hasMany', 'is_correct' => true, 'order_index' => 1],
            ['body' => 'connectsTo', 'is_correct' => false, 'order_index' => 2],
            ['body' => 'belongsTo', 'is_correct' => true, 'order_index' => 3],
            ['body' => 'linksThrough', 'is_correct' => false, 'order_index' => 4],
        ]);

        $lq4 = $quiz2->questions()->create([
            'question_type_id' => $types['number_input']->id,
            'body' => '<p>What HTTP status code means "Not Found"?</p>',
            'marks' => 1,
            'order_index' => 4,
        ]);
        $lq4->answerKey()->create([
            'correct_number_value' => 404,
            'number_tolerance' => 0,
            'match_strategy' => 'exact',
        ]);

        $lq5 = $quiz2->questions()->create([
            'question_type_id' => $types['text_input']->id,
            'body' => '<p>What facade is used to interact with the database in Laravel?</p>',
            'marks' => 1,
            'order_index' => 5,
        ]);
        $lq5->answerKey()->create([
            'correct_text_value' => 'DB',
            'match_strategy' => 'case_insensitive',
        ]);

        // Seed 2 attempts for quiz 2
        $this->seedAttempt($quiz2, $user, [
            $lq1->id => ['option' => $lq1->options->where('is_correct', true)->first()->id],
            $lq2->id => ['option' => $lq2->options->where('is_correct', true)->first()->id],
            $lq3->id => ['options' => $lq3->options->where('is_correct', true)->pluck('id')->toArray()],
            $lq4->id => ['number' => 404],
            $lq5->id => ['text' => 'DB'],
        ], allCorrect: true);

        $this->seedAttempt($quiz2, $user, [
            $lq1->id => ['option' => $lq1->options->where('is_correct', true)->first()->id],
            $lq2->id => ['option' => $lq2->options->where('is_correct', false)->first()->id],
            $lq3->id => ['options' => [$lq3->options->where('is_correct', true)->first()->id]],
            $lq4->id => ['number' => 400],
            $lq5->id => ['text' => 'db'],
        ], allCorrect: false);
    }

    private function seedAttempt(Quiz $quiz, User $user, array $answerData, bool $allCorrect): void
    {
        $attempt = Attempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'submitted_at' => now()->subMinutes(rand(10, 10000)),
        ]);

        $totalScore = 0;

        foreach ($quiz->questions()->with(['questionType', 'options', 'answerKey'])->get() as $question) {
            $data = $answerData[$question->id] ?? [];
            $slug = $question->questionType->slug;

            $answer = Answer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'text_value' => $data['text'] ?? null,
                'number_value' => $data['number'] ?? null,
            ]);

            if (isset($data['option'])) {
                $answer->selectedOptions()->sync([$data['option']]);
            } elseif (isset($data['options'])) {
                $answer->selectedOptions()->sync($data['options']);
            }

            // Evaluate
            $isCorrect = false;
            $marksAwarded = 0.0;

            if (in_array($slug, ['binary', 'single_choice'])) {
                $selectedId = $data['option'] ?? null;
                $correctId = $question->options->where('is_correct', true)->first()?->id;
                $isCorrect = $selectedId && $selectedId === $correctId;

            } elseif ($slug === 'multiple_choice') {
                $selectedIds = collect($data['options'] ?? [])->sort()->values()->toArray();
                $correctIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
                $isCorrect = $selectedIds === $correctIds;

            } elseif ($slug === 'number_input') {
                $key = $question->answerKey;
                $submitted = $data['number'] ?? null;
                $isCorrect = $submitted !== null
                    && abs($submitted - $key->correct_number_value) <= ($key->number_tolerance ?? 0);

            } elseif ($slug === 'text_input') {
                $key = $question->answerKey;
                $submitted = strtolower(trim($data['text'] ?? ''));
                $correct = strtolower(trim($key->correct_text_value ?? ''));
                $isCorrect = $submitted === $correct;
            }

            $marksAwarded = $isCorrect ? (float)$question->marks : 0.0;
            $totalScore += $marksAwarded;

            $answer->update([
                'is_correct' => $isCorrect,
                'marks_awarded' => $marksAwarded,
            ]);
        }

        $attempt->update(['total_score' => $totalScore]);
    }
}
