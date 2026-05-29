<?php

namespace App\Http\Requests;

use App\Enums\MatchStrategy;
use App\Models\QuestionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_type_id' => ['required', 'integer', 'exists:question_types,id'],
            'questions.*.body' => ['required', 'string'],
            'questions.*.marks' => ['nullable', 'numeric', 'min:0'],

            // Media
            'questions.*.image' => ['nullable', 'image', 'max:5120'],
            'questions.*.video_url' => ['nullable', 'url', 'max:2048'],

            // Options (choice-based types)
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*.body' => ['nullable', 'string', 'max:1000'],
            'questions.*.options.*.image' => ['nullable', 'image', 'max:2048'],
            'questions.*.options.*.is_correct' => ['nullable', 'boolean'],

            // Answer keys (text / number input)
            'questions.*.answer_key.correct_text_value' => ['nullable', 'string', 'max:1000'],
            'questions.*.answer_key.correct_number_value' => ['nullable', 'numeric'],
            'questions.*.answer_key.number_tolerance' => ['nullable', 'numeric', 'min:0'],
            'questions.*.answer_key.match_strategy' => ['nullable', Rule::enum(MatchStrategy::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'questions.required' => 'A quiz must have at least one question.',
            'questions.min' => 'A quiz must have at least one question.',

            'questions.*.question_type_id.required' => 'Question :position: please select a type.',
            'questions.*.question_type_id.exists' => 'Question :position: invalid question type.',
            'questions.*.body.required' => 'Question :position: body is required.',
            'questions.*.marks.numeric' => 'Question :position: marks must be a number.',
            'questions.*.marks.min' => 'Question :position: marks cannot be negative.',

            'questions.*.image.image' => 'Question :position: image must be a valid image file.',
            'questions.*.image.max' => 'Question :position: image must not exceed 5MB.',
            'questions.*.video_url.url' => 'Question :position: video URL must be a valid URL.',

            'questions.*.answer_key.match_strategy.in' => 'Question :position: invalid match strategy.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $questions = $this->input('questions', []);

            foreach ($questions as $i => $q) {
                $typeId = $q['question_type_id'] ?? null;
                if (!$typeId) continue;

                /** @var \App\Models\QuestionType|null $type */
                $type = QuestionType::find($typeId);
                if (!$type) continue;

                $prefix = "questions.{$i}";

                // Choice-based: must have ≥ 2 options, exactly one correct for single/binary
                if ($type->has_options) {
                    $options = $q['options'] ?? [];

                    if (count($options) < 2) {
                        $v->errors()->add("{$prefix}.options", "Question " . ($i + 1) . ": must have at least 2 options.");
                    }

                    $correctCount = collect($options)->filter(fn($o) => filter_var($o['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN))->count();

                    if (!$type->allows_multiple_correct && $correctCount !== 1) {
                        $v->errors()->add("{$prefix}.options", "Question " . ($i + 1) . ": exactly one correct option required.");
                    }

                    if ($type->allows_multiple_correct && $correctCount < 1) {
                        $v->errors()->add("{$prefix}.options", "Question " . ($i + 1) . ": at least one correct option required.");
                    }

                    // Each option must have a body or image
                    foreach ($options as $j => $opt) {
                        $hasBody = !empty(trim($opt['body'] ?? ''));
                        $hasImage = $this->hasFile("questions.{$i}.options.{$j}.image")
                            || !empty($opt['image']);
                        if (!$hasBody && !$hasImage) {
                            $v->errors()->add(
                                "{$prefix}.options.{$j}.body",
                                "Question " . ($i + 1) . ", option " . ($j + 1) . ": text or image required."
                            );
                        }
                    }
                }

                // Number input: correct_number_value required
                if ($type->slug === 'number_input') {
                    if (!isset($q['answer_key']['correct_number_value']) || $q['answer_key']['correct_number_value'] === '') {
                        $v->errors()->add("{$prefix}.answer_key.correct_number_value",
                            "Question " . ($i + 1) . ": correct number value is required.");
                    }
                }

                // Text input: correct_text_value required
                if ($type->slug === 'text_input') {
                    if (empty(trim($q['answer_key']['correct_text_value'] ?? ''))) {
                        $v->errors()->add("{$prefix}.answer_key.correct_text_value",
                            "Question " . ($i + 1) . ": correct text answer is required.");
                    }
                }
            }
        });
    }
}
