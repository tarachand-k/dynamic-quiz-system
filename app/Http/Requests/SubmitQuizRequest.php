<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*.option_ids' => ['nullable', 'array'],
            'answers.*.option_ids.*' => ['integer', 'exists:options,id'],
            'answers.*.text_value' => ['nullable', 'string', 'max:5000'],
            'answers.*.number_value' => ['nullable', 'numeric'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $quiz = $this->route('quiz');
            $quiz->loadMissing('questions.questionType');

            $answeredIds = array_keys($this->input('answers', []));

            foreach ($quiz->questions as $question) {
                if (!in_array($question->id, $answeredIds)) {
                    $v->errors()->add(
                        "answers.{$question->id}",
                        "Question {$question->order_index}: please answer all questions before submitting."
                    );
                    continue;
                }

                $data = $this->input("answers.{$question->id}", []);
                $slug = $question->questionType->slug;

                if (in_array($slug, ['binary', 'single_choice', 'multiple_choice'])) {
                    if (empty($data['option_ids'])) {
                        $v->errors()->add(
                            "answers.{$question->id}",
                            "Question {$question->order_index}: please select an answer."
                        );
                    }
                } elseif ($slug === 'text_input') {
                    if (empty(trim($data['text_value'] ?? ''))) {
                        $v->errors()->add(
                            "answers.{$question->id}",
                            "Question {$question->order_index}: please enter an answer."
                        );
                    }
                } elseif ($slug === 'number_input') {
                    if (!isset($data['number_value']) || $data['number_value'] === '') {
                        $v->errors()->add(
                            "answers.{$question->id}",
                            "Question {$question->order_index}: please enter a number."
                        );
                    }
                }
            }
        });
    }
}
