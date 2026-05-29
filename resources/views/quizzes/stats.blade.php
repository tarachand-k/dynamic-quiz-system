<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('quizzes.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-base font-semibold text-gray-900">Stats: {{ $quiz->title }}</h1>
        </div>
    </x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('quizzes.show', $quiz) }}"
           class="text-sm text-gray-600 border border-gray-300 hover:bg-gray-50 px-3 py-1.5 rounded transition-colors">
            Preview
        </a>
        <a href="{{ route('quizzes.edit', $quiz) }}"
           class="text-sm text-indigo-600 border border-indigo-300 hover:bg-indigo-50 px-3 py-1.5 rounded transition-colors">
            Edit Quiz
        </a>
    </x-slot>

    <div class="p-4 sm:p-6 space-y-6 max-w-5xl mx-auto">

        {{-- ── Summary cards ── --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $submitted = $attempts->whereNotNull('submitted_at');
                $avgScore  = $submitted->count() > 0
                    ? round($submitted->avg('total_score'), 1)
                    : null;
                $maxScore  = $submitted->count() > 0
                    ? round($submitted->max('total_score'), 1)
                    : null;
                $passRate  = $submitted->count() > 0
                    ? round(($submitted->filter(fn($a) => $a->total_score >= ($quiz->total_marks * 0.7))->count() / $submitted->count()) * 100)
                    : null;
            @endphp

            <div class="bg-white rounded border border-gray-200 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600">{{ $attempts->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Attempts</p>
            </div>
            <div class="bg-white rounded border border-gray-200 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ $maxScore ?? '—' }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    Highest Score
                    <span class="text-gray-300">/ {{ $quiz->total_marks }}</span>
                </p>
            </div>
            <div class="bg-white rounded border border-gray-200 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $avgScore ?? '—' }}</p>
                <p class="text-xs text-gray-500 mt-1">Avg Score <span
                        class="text-gray-300">/ {{ $quiz->total_marks }}</span></p>
            </div>
            <div class="bg-white rounded border border-gray-200 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold {{ $passRate >= 70 ? 'text-green-600' : 'text-amber-500' }}">
                    {{ $passRate !== null ? $passRate.'%' : '—' }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Pass Rate (≥70%)</p>
            </div>
        </div>

        {{-- ── Per-question accuracy ── --}}
        @if($questionStats->count() > 0)
            <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-800">Question Accuracy</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Based on {{ $submitted->count() }} completed attempt(s)</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($questionStats as $stat)
                        <div class="px-5 py-3">
                            <div class="flex items-center justify-between gap-4 mb-1.5">
                                <span class="text-xs text-gray-600 flex-1 truncate">
                                    <span class="font-medium text-gray-500 mr-1">Q{{ $loop->iteration }}.</span>
                                    {!! strip_tags($stat['question']) !!}
                                </span>
                                <span class="text-xs font-semibold shrink-0
                                    {{ $stat['accuracy'] >= 70 ? 'text-green-600' : ($stat['accuracy'] >= 40 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ $stat['accuracy'] }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded h-1.5">
                                <div class="h-1.5 rounded transition-all
                                    {{ $stat['accuracy'] >= 70 ? 'bg-green-500' : ($stat['accuracy'] >= 40 ? 'bg-amber-400' : 'bg-red-400') }}"
                                     style="width: {{ $stat['accuracy'] }}%"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $stat['correct'] }} / {{ $stat['total'] }}
                                correct</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Attempt history ── --}}
        <div class="bg-white rounded border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Attempt History</h2>
            </div>
            @if($submitted->count() === 0)
                <p class="text-sm text-gray-400 text-center py-10">No completed attempts yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider">#
                            </th>
                            <th class="px-4 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider">
                                Score
                            </th>
                            <th class="px-4 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider">
                                Percentage
                            </th>
                            <th class="px-4 py-2.5 text-left font-semibold text-gray-500 uppercase tracking-wider">
                                Submitted
                            </th>
                            <th class="px-4 py-2.5"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        @foreach($submitted->sortByDesc('submitted_at') as $attempt)
                            @php
                                $pct = $quiz->total_marks > 0
                                    ? round(($attempt->total_score / $quiz->total_marks) * 100)
                                    : 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-gray-400">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2.5 font-medium text-gray-800">{{ $attempt->total_score }}
                                    / {{ $quiz->total_marks }}</td>
                                <td class="px-4 py-2.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $pct >= 70 ? 'bg-green-100 text-green-700' : ($pct >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $pct }}%
                                        </span>
                                </td>
                                <td class="px-4 py-2.5 text-gray-500">{{ $attempt->submitted_at?->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('attempts.result', $attempt) }}"
                                       class="text-indigo-500 hover:text-indigo-700 font-medium">View</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
