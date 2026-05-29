<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizService
{
    public function create(array $data, int $userId): Quiz
    {
        return DB::transaction(function () use ($data, $userId) {
            $quiz = Quiz::create([
                'user_id' => $userId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
            ]);

            foreach ($data['questions'] ?? [] as $index => $qData) {
                $this->createQuestion($quiz, $qData, $index + 1);
            }

            return $quiz;
        });
    }

    public function update(Quiz $quiz, array $data): Quiz
    {
        return DB::transaction(function () use ($quiz, $data) {
            $quiz->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
            ]);

            $quiz->load('questions.media', 'questions.options');

            foreach ($quiz->questions as $question) {
                $this->deleteQuestionMedia($question);
            }
            $quiz->questions()->delete();

            foreach ($data['questions'] ?? [] as $index => $qData) {
                $this->createQuestion($quiz, $qData, $index + 1);
            }

            return $quiz->fresh('questions');
        });
    }

    public function delete(Quiz $quiz): void
    {
        DB::transaction(function () use ($quiz) {
            foreach ($quiz->questions as $question) {
                $this->deleteQuestionMedia($question);
            }
            $quiz->delete();
        });
    }

    private function createQuestion(Quiz $quiz, array $data, int $orderIndex): Question
    {
        $question = $quiz->questions()->create([
            'question_type_id' => $data['question_type_id'],
            'body' => $data['body'],
            'marks' => $data['marks'] ?? 1.00,
            'order_index' => $orderIndex,
        ]);

        $this->handleQuestionMedia($question, $data);
        $this->handleOptions($question, $data);
        $this->handleAnswerKey($question, $data);

        return $question;
    }

    private function handleQuestionMedia(Question $question, array $data): void
    {
        // Image upload
        if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
            $path = $data['image']->store('question-media', 'public');
            $question->media()->create([
                'media_type' => 'image',
                'url' => $path,
            ]);
        }

        // Video URL
        if (!empty($data['video_url'])) {
            $question->media()->create([
                'media_type' => 'video',
                'url' => $data['video_url'],
            ]);
        }
    }

    private function handleOptions(Question $question, array $data): void
    {
        if (empty($data['options'])) {
            return;
        }

        foreach ($data['options'] as $index => $optionData) {
            $mediaUrl = null;

            if (!empty($optionData['image']) && $optionData['image'] instanceof UploadedFile) {
                $mediaUrl = $optionData['image']->store('option-media', 'public');
            }

            $question->options()->create([
                'body' => $optionData['body'] ?? null,
                'media_url' => $mediaUrl,
                'is_correct' => (bool)($optionData['is_correct'] ?? false),
                'order_index' => $index + 1,
            ]);
        }
    }

    private function handleAnswerKey(Question $question, array $data): void
    {
        if (empty($data['answer_key'])) {
            return;
        }

        $key = $data['answer_key'];

        $question->answerKey()->create([
            'correct_text_value' => $key['correct_text_value'] ?? null,
            'correct_number_value' => $key['correct_number_value'] ?? null,
            'number_tolerance' => $key['number_tolerance'] ?? 0,
            'match_strategy' => $key['match_strategy'] ?? 'case_insensitive',
        ]);
    }

    private function deleteQuestionMedia(Question $question): void
    {
        foreach ($question->media as $media) {
            if ($media->media_type === MediaType::Image) {
                Storage::disk('public')->delete($media->url);
            }
        }

        foreach ($question->options as $option) {
            if ($option->media_url) {
                Storage::disk('public')->delete($option->media_url);
            }
        }
    }
}
