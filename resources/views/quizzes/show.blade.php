<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('quizzes.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-base font-semibold text-gray-900 truncate max-w-xs">{{ $quiz->title }}</h1>
        </div>
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('quizzes.edit', $quiz) }}"
           class="text-sm border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded transition-colors">
            Edit
        </a>
        <a href="{{ route('quiz.attempt', $quiz) }}" target="_blank"
           class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded transition-colors">
            Preview
        </a>
        @if($quiz->attempts()->whereNotNull('submitted_at')->exists())
            <a href="{{ route('quizzes.stats', $quiz) }}"
               class="text-sm border border-indigo-300 text-indigo-600 hover:bg-indigo-50 px-3 py-1.5 rounded transition-colors">
                Stats
            </a>
        @endif
    </x-slot>

    <div class="p-4 sm:p-6 max-w-4xl mx-auto space-y-4">

        {{-- Share card --}}
        <div class="bg-indigo-50 border border-indigo-200 rounded p-4 flex items-center gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-indigo-600 mb-1">Share Link</p>
                <p class="text-sm text-indigo-800 truncate font-mono">{{ route('quiz.attempt', $quiz) }}</p>
            </div>
            <button
                onclick="navigator.clipboard.writeText('{{ route('quiz.attempt', $quiz) }}').then(() => { this.textContent = '✓ Copied'; setTimeout(() => this.textContent = 'Copy', 1500) })"
                class="text-sm font-medium text-indigo-600 hover:text-indigo-700 whitespace-nowrap border border-indigo-300 px-3 py-1.5 rounded hover:bg-indigo-100 transition-colors">
                Copy
            </button>
        </div>

        {{-- Questions --}}
        @foreach($quiz->questions as $question)
            <div class="bg-white rounded border border-gray-200 shadow-sm p-5">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span
                            class="text-xs font-medium bg-gray-100 text-gray-500 px-2 py-0.5 rounded">Q{{ $loop->iteration }}</span>
                        <span
                            class="text-xs font-medium bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded">{{ $question->questionType->label }}</span>
                    </div>
                    <span class="text-xs text-gray-400 whitespace-nowrap shrink-0">{{ $question->marks }} mark(s)</span>
                </div>

                <div class="prose prose-sm max-w-none text-gray-800 mb-4">{!! $question->body !!}</div>

                @if($question->imageMedia)
                    <img src="{{ Storage::url($question->imageMedia->url) }}"
                         class="mb-3 rounded max-h-48 object-cover border border-gray-200">
                @endif
                @if($question->videoMedia)
                    <p class="text-xs text-gray-500 mb-3">
                        📹 <a href="{{ $question->videoMedia->url }}" target="_blank"
                             class="text-indigo-600 hover:underline">{{ $question->videoMedia->url }}</a>
                    </p>
                @endif

                @if($question->questionType->has_options)
                    <div class="space-y-1.5">
                        @foreach($question->options as $option)
                            <div class="flex items-center gap-3 px-3 py-2 rounded border
                                {{ $option->is_correct ? 'bg-green-50 border-green-300' : 'bg-gray-50 border-gray-200' }}">
                                <span class="{{ $option->is_correct ? 'text-green-500' : 'text-gray-300' }} text-sm">
                                    {{ $option->is_correct ? '✓' : '○' }}
                                </span>
                                @if($option->body)
                                    <span class="text-sm text-gray-800">{{ $option->body }}</span>
                                @endif
                                @if($option->media_url)
                                    <img src="{{ Storage::url($option->media_url) }}"
                                         class="h-8 w-auto rounded object-cover">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif($question->answerKey)
                    <div class="bg-green-50 border border-green-200 rounded px-3 py-2 text-sm text-green-800">
                        @if($question->questionType->slug === 'text_input')
                            Answer: <strong>{{ $question->answerKey->correct_text_value }}</strong>
                            <span
                                class="text-xs text-green-600">({{ $question->answerKey->match_strategy->value }})</span>
                        @elseif($question->questionType->slug === 'number_input')
                            Answer: <strong>{{ $question->answerKey->correct_number_value }}</strong>
                            @if($question->answerKey->number_tolerance > 0)
                                ± {{ $question->answerKey->number_tolerance }}
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</x-app-layout>
