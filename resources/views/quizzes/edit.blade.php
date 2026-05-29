<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('quizzes.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-base font-semibold text-gray-900">Edit Quiz: {{ $quiz->title }}</h1>
        </div>
    </x-slot>

    @php
        // After a validation failure, old() takes precedence so the user keeps their edits.
        // On a fresh edit page load, we hydrate from the DB record.
        if (old('title')) {
            $initialData = [
                'title'       => old('title', $quiz->title),
                'description' => old('description', $quiz->description ?? ''),
                'questions'   => collect(old('questions', []))->map(function ($q) {
                    return [
                        'question_type_id'   => $q['question_type_id'] ?? 2,
                        'body'               => $q['body'] ?? '',
                        'marks'              => $q['marks'] ?? 1,
                        'video_url'          => $q['video_url'] ?? '',
                        'existing_image'     => null,
                        'correctOptionIndex' => 0,
                        'options'            => collect($q['options'] ?? [])->map(fn($o) => [
                            'body'           => $o['body'] ?? '',
                            'is_correct'     => isset($o['is_correct']),
                            'existing_image' => null,
                        ])->values()->toArray(),
                        'answer_key'         => [
                            'correct_text_value'   => $q['answer_key']['correct_text_value'] ?? '',
                            'correct_number_value' => $q['answer_key']['correct_number_value'] ?? '',
                            'number_tolerance'     => $q['answer_key']['number_tolerance'] ?? 0,
                            'match_strategy'       => $q['answer_key']['match_strategy'] ?? 'case_insensitive',
                        ],
                    ];
                })->values()->toArray(),
            ];
        } else {
            $initialData = [
                'title'       => $quiz->title,
                'description' => $quiz->description ?? '',
                'questions'   => $quiz->questions->map(function ($q) {
                    return [
                        'question_type_id'   => $q->question_type_id,
                        'body'               => $q->body,
                        'marks'              => $q->marks,
                        'video_url'          => $q->videoMedia?->url ?? '',
                        'existing_image'     => $q->imageMedia
                            ? Storage::url($q->imageMedia->url)
                            : null,
                        'correctOptionIndex' => $q->options->search(fn($o) => $o->is_correct) ?: 0,
                        'options'            => $q->options->map(fn($o) => [
                            'body'           => $o->body ?? '',
                            'is_correct'     => (bool) $o->is_correct,
                            'existing_image' => $o->media_url
                                ? Storage::url($o->media_url)
                                : null,
                        ])->values()->toArray(),
                        'answer_key'         => $q->answerKey ? [
                            'correct_text_value'   => $q->answerKey->correct_text_value ?? '',
                            'correct_number_value' => $q->answerKey->correct_number_value ?? '',
                            'number_tolerance'     => $q->answerKey->number_tolerance ?? 0,
                            'match_strategy'       => $q->answerKey->match_strategy->value ?? 'case_insensitive',
                        ] : null,
                    ];
                })->values()->toArray(),
            ];
        }
    @endphp

    <form action="{{ route('quizzes.update', $quiz) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('quizzes._form')
    </form>
</x-app-layout>
