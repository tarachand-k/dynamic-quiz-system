<x-quiz-layout>
    @php
        $totalMarks = $attempt->quiz->total_marks;
        $percentage = $totalMarks > 0
            ? round(($attempt->total_score / $totalMarks) * 100)
            : 0;
    @endphp

    {{-- Back --}}
    <div class="mb-4">
        <a href="{{ url('/') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            ← Home
        </a>
    </div>

    {{-- Score card --}}
    <div class="text-center mb-8 bg-white border border-gray-200 p-6 rounded shadow-sm p-6">
        <div class="inline-flex items-center justify-center w-20 h-20 mb-3 rounded-full
            {{ $percentage >= 70 ? 'bg-green-50 text-green-600' : ($percentage >= 40 ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600') }}">
            <span class="text-3xl font-bold">{{ $percentage }}%</span>
        </div>
        <h1 class="text-xl font-bold text-gray-900">
            {{ $percentage >= 70 ? 'Great job!' : ($percentage >= 40 ? 'Not bad!' : 'Keep practicing!') }}
        </h1>
        <p class="text-gray-500 mt-1 text-sm">
            You scored <strong>{{ $attempt->total_score }}</strong>
            out of <strong>{{ $totalMarks }}</strong> marks
        </p>
        <p class="text-xs text-gray-400 mt-1">
            Submitted {{ $attempt->submitted_at->diffForHumans() }}
        </p>
    </div>

    {{-- Per-question breakdown --}}
    <div class="space-y-3">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Question Breakdown</h2>

        @foreach($attempt->answers->sortBy('question.order_index') as $answer)
            @php $question = $answer->question; @endphp
            <div class="bg-white border rounded shadow-sm p-4
                {{ $answer->is_correct ? 'border-l-4 border-l-green-500 border-gray-200' : 'border-l-4 border-l-red-400 border-gray-200' }}">

                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span
                                class="{{ $answer->is_correct ? 'text-green-500' : 'text-red-500' }} font-bold text-sm">
                                {{ $answer->is_correct ? '✓' : '✗' }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $question->questionType->label }}</span>
                            @if($question->marks == 0)
                                <span class="text-xs text-gray-400 italic">(unscored)</span>
                            @endif
                        </div>

                        <div class="prose prose-sm max-w-none text-gray-800 mb-2">
                            {!! $question->body !!}
                        </div>

                        {{-- Show what was selected --}}
                        @if($question->questionType->has_options)
                            @php
                                $selectedIds = $answer->selectedOptions->pluck('id')->toArray();
                                $correctIds  = $question->options->where('is_correct', true)->pluck('id')->toArray();
                            @endphp
                            <div class="space-y-1">
                                @foreach($question->options as $opt)
                                    @php
                                        $wasSelected = in_array($opt->id, $selectedIds);
                                        $isCorrect   = $opt->is_correct;
                                    @endphp
                                    @if($wasSelected || $isCorrect)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs rounded
                                            @if($wasSelected && $isCorrect) bg-green-100 text-green-800
                                            @elseif($wasSelected && !$isCorrect) bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-600
                                            @endif">
                                            @if($wasSelected && $isCorrect)
                                                ✓ Selected (correct)
                                            @elseif($wasSelected && !$isCorrect)
                                                ✗ Selected (wrong)
                                            @else
                                                ○ Correct answer (not selected)
                                            @endif
                                            — {{ $opt->body ?? '[image]' }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @elseif($question->questionType->slug === 'text_input')
                            <p class="text-sm text-gray-700">
                                Your answer: <strong>{{ $answer->text_value ?? '—' }}</strong>
                            </p>
                            @if($question->answerKey && !$answer->is_correct)
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Expected: <em>{{ $question->answerKey->correct_text_value }}</em>
                                </p>
                            @endif
                        @elseif($question->questionType->slug === 'number_input')
                            <p class="text-sm text-gray-700">
                                Your answer: <strong>{{ $answer->number_value ?? '—' }}</strong>
                            </p>
                            @if($question->answerKey && !$answer->is_correct)
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Expected: <em>{{ $question->answerKey->correct_number_value }}</em>
                                    @if($question->answerKey->number_tolerance > 0)
                                        ± {{ $question->answerKey->number_tolerance }}
                                    @endif
                                </p>
                            @endif
                        @endif
                    </div>

                    {{-- Marks badge --}}
                    <div class="text-right shrink-0">
                        <span class="text-base font-bold
                            {{ $answer->is_correct ? 'text-green-600' : 'text-red-500' }}">
                            {{ $answer->marks_awarded }}
                        </span>
                        <span class="text-xs text-gray-400"> / {{ $question->marks }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex gap-3 justify-end">
        <a href="{{ route('quiz.attempt', $attempt->quiz) }}"
           class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 transition-colors">
            Try Again
        </a>
        <a href="{{ url('/') }}"
           class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded text-white text-sm font-medium transition-colors">
            Go Home
        </a>
    </div>
</x-quiz-layout>
