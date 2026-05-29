<x-quiz-layout>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">{{ $quiz->title }}</h1>
        @if($quiz->description)
            <p class="text-gray-500 mt-1 text-sm">{{ $quiz->description }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-1">
            {{ $quiz->questions->count() }} questions
            · {{ number_format($quiz->total_marks, 0) }} marks total
        </p>
    </div>

    <form action="{{ route('quiz.attempt.submit', $quiz) }}" method="POST">
        @csrf
        
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm mb-4">
                <p class="font-medium mb-1">Please answer all questions before submitting:</p>
                <ul class="space-y-0.5 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-5">
            @foreach($quiz->questions as $question)
                <div class="bg-white border border-gray-200 p-5 rounded shadow-sm p-5">

                    {{-- Header --}}
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-medium bg-gray-100 text-gray-500 px-2 py-0.5 rounded">
                            {{ $loop->iteration }} / {{ $quiz->questions->count() }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $question->marks }} mark(s)</span>
                    </div>

                    <div class="prose prose-sm max-w-none text-gray-800 mb-3">
                        {!! $question->body !!}
                    </div>

                    {{-- Media --}}
                    @if($question->imageMedia)
                        <img src="{{ Storage::url($question->imageMedia->url) }}"
                             class="mb-3 max-h-48 object-cover border border-gray-200 rounded">
                    @endif
                    @if($question->videoMedia)
                        <div class="mb-3">
                            <a href="{{ $question->videoMedia->url }}" target="_blank"
                               class="text-sm text-indigo-600 hover:underline">▶ Watch video</a>
                        </div>
                    @endif

                    {{-- Choice options --}}
                    @if($question->questionType->has_options)
                        <div class="space-y-2">
                            @foreach($question->options as $option)
                                <label class="flex items-center gap-3 px-3 py-2.5 border border-gray-200 rounded
                                              cursor-pointer hover:bg-indigo-50 hover:border-indigo-300
                                              has-[:checked]:bg-indigo-50 has-[:checked]:border-indigo-400">
                                    @if($question->questionType->allows_multiple_correct)
                                        <input type="checkbox"
                                               name="answers[{{ $question->id }}][option_ids][]"
                                               value="{{ $option->id }}"
                                               class="border-gray-300 text-indigo-600 focus:ring-indigo-500 shrink-0">
                                    @else
                                        <input type="radio"
                                               name="answers[{{ $question->id }}][option_ids][]"
                                               value="{{ $option->id }}"
                                               class="border-gray-300 text-indigo-600 focus:ring-indigo-500 shrink-0">
                                    @endif
                                    @if($option->body)
                                        <span class="text-sm text-gray-800">{{ $option->body }}</span>
                                    @endif
                                    @if($option->media_url)
                                        <img src="{{ Storage::url($option->media_url) }}"
                                             class="h-10 w-auto object-cover rounded">
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @endif

                    {{-- Text input --}}
                    @if($question->questionType->slug === 'text_input')
                        <input type="text"
                               name="answers[{{ $question->id }}][text_value]"
                               class="w-full border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 text-sm px-3 py-2 border"
                               placeholder="Type your answer here...">
                    @endif

                    {{-- Number input --}}
                    @if($question->questionType->slug === 'number_input')
                        <input type="number" step="any"
                               name="answers[{{ $question->id }}][number_value]"
                               class="w-full border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm px-3 py-2 border"
                               placeholder="Enter a number...">
                    @endif
                </div>
            @endforeach


            <div class="flex justify-end pt-2">
                <button type="submit"
                        class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded transition-colors">
                    Submit Quiz
                </button>
            </div>
        </div>
    </form>
</x-quiz-layout>
