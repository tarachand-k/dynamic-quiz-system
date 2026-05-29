<x-app-layout>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-gray-900">My Quizzes</h1>
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('quizzes.create') }}"
           class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3.5 py-1.5 rounded transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Quiz
        </a>
    </x-slot>

    <div class="p-4 sm:p-6">
        @if(session('success'))
            <div
                class="mb-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if($quizzes->isEmpty())
            <div class="text-center py-20 bg-white rounded border border-dashed border-gray-300">
                <svg class="w-10 h-10 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500 mb-4 text-sm">You haven't created any quizzes yet.</p>
                <a href="{{ route('quizzes.create') }}"
                   class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-700 font-medium text-sm">
                    Create your first quiz →
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($quizzes as $quiz)
                    <div
                        class="bg-white rounded border border-gray-200 shadow-sm hover:shadow-md transition-shadow flex flex-col">
                        <div class="p-4 flex-1">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900 text-sm leading-snug">{{ $quiz->title }}</h3>
                            </div>
                            @if($quiz->description)
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $quiz->description }}</p>
                            @endif

                            {{-- Stats row --}}
                            <div class="flex items-center gap-3 mt-3">
                                <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01"/>
                                    </svg>
                                    {{ $quiz->questions_count }} questions
                                </span>
                                <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857"/>
                                    </svg>
                                    {{ $quiz->attempts_count }} attempts
                                </span>
                                @if($quiz->attempts_count > 0)
                                    <a href="{{ route('quizzes.stats', $quiz) }}"
                                       class="inline-flex items-center gap-0.5 text-xs text-indigo-500 hover:text-indigo-700 ml-auto">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        Stats
                                    </a>
                                @endif
                            </div>

                            {{-- Share link --}}
                            <div class="flex items-center gap-1.5 bg-gray-50 rounded px-2.5 py-1.5 mt-3">
                                <span class="text-xs text-gray-400 truncate flex-1 font-mono">
                                    {{ route('quiz.attempt', $quiz) }}
                                </span>
                                <button
                                    onclick="navigator.clipboard.writeText('{{ route('quiz.attempt', $quiz) }}').then(() => { this.textContent = '✓'; setTimeout(() => this.textContent = 'Copy', 1500) })"
                                    class="text-xs text-indigo-600 hover:text-indigo-700 font-medium whitespace-nowrap">
                                    Copy
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 px-3 pb-3 pt-1 border-t border-gray-100 mt-1">
                            <a href="{{ route('quizzes.show', $quiz) }}"
                               class="flex-1 text-center text-xs font-medium text-gray-600 hover:text-indigo-600 py-1.5 rounded hover:bg-gray-50 transition-colors">
                                Preview
                            </a>
                            <a href="{{ route('quizzes.edit', $quiz) }}"
                               class="flex-1 text-center text-xs font-medium text-gray-600 hover:text-indigo-600 py-1.5 rounded hover:bg-gray-50 transition-colors">
                                Edit
                            </a>
                            @if($quiz->attempts_count > 0)
                                <a href="{{ route('quizzes.stats', $quiz) }}"
                                   class="flex-1 text-center text-xs font-medium text-gray-600 hover:text-indigo-600 py-1.5 rounded hover:bg-gray-50 transition-colors">
                                    Stats
                                </a>
                            @endif
                            <form action="{{ route('quizzes.destroy', $quiz) }}" method="POST"
                                  onsubmit="return confirm('Delete this quiz and all its data?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-xs font-medium text-red-500 hover:text-red-700 py-1.5 px-2 rounded hover:bg-red-50 transition-colors">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-5">{{ $quizzes->links() }}</div>
        @endif
    </div>
</x-app-layout>
