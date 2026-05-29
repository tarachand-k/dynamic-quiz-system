<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-gray-900">Create Quiz</h1>
    </x-slot>

    @php
        // Preserve old input after validation failure
        $initialData = old() ? [
            'title'       => old('title', ''),
            'description' => old('description', ''),
            'questions'   => collect(old('questions', []))->map(function ($q, $i) {
                return [
                    'question_type_id'  => $q['question_type_id'] ?? 2,
                    'body'              => $q['body'] ?? '',
                    'marks'             => $q['marks'] ?? 1,
                    'video_url'         => $q['video_url'] ?? '',
                    'existing_image'    => null,
                    'correctOptionIndex'=> 0,
                    'options'           => collect($q['options'] ?? [])->map(fn($o) => [
                        'body'           => $o['body'] ?? '',
                        'is_correct' => filter_var($o['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'existing_image' => null,
                    ])->values()->toArray(),
                    'answer_key'        => [
                        'correct_text_value'   => $q['answer_key']['correct_text_value'] ?? '',
                        'correct_number_value' => $q['answer_key']['correct_number_value'] ?? '',
                        'number_tolerance'     => $q['answer_key']['number_tolerance'] ?? 0,
                        'match_strategy'       => $q['answer_key']['match_strategy'] ?? 'case_insensitive',
                    ],
                ];
            })->values()->toArray(),
        ] : null;
    @endphp

    <form action="{{ route('quizzes.store') }}"
          method="POST"
          enctype="multipart/form-data"
    >
        @csrf
        @include('quizzes._form')
    </form>
</x-app-layout>
