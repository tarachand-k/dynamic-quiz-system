@php
    $questionTypes = \App\Models\QuestionType::all();
    // Safe JS-encoded initial data — avoids Alpine "missing ) after argument list" errors
    // when quiz titles/bodies contain quotes, HTML entities, etc.
    $safeInitial = json_encode($initialData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    $safeTypes   = json_encode($questionTypes->values()->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
@endphp

{{-- Quill rich-text editor CSS --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6" x-data="quizBuilder()" x-init="init()">
    @if($errors->any())
        <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded text-sm">
            ⚠️ Please fix the errors below. Any uploaded images will need to be re-selected.
        </div>
    @endif

    {{-- ── QUIZ META ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded border border-gray-200 shadow-sm p-5 mb-5">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Quiz Details</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" x-model="title"
                       class="w-full border rounded shadow-sm text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                              @error('title') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                       placeholder="e.g. General Knowledge Quiz">
                @error('title')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" x-model="description" rows="2"
                          class="w-full border rounded shadow-sm text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                 @error('description') border-red-400 bg-red-50 @else border-gray-300 @enderror"
                          placeholder="Optional description..."></textarea>
                @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- ── QUESTIONS ──────────────────────────────────────────────────── --}}
    {{-- Top question counter --}}
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
            Questions <span class="text-indigo-600 font-bold" x-text="`(${questions.length})`"></span>
        </span>
        <span class="text-xs text-gray-400" x-text="`${totalMarks().toFixed(1)} marks total`"></span>
    </div>

    {{-- Backend question errors (generic) --}}
    @if($errors->hasAny(['questions', 'questions.*']))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
            <ul class="space-y-0.5">
                @foreach($errors->get('questions.*') as $msgs)
                    @foreach($msgs as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                @endforeach
                @foreach($errors->get('questions') as $msg)
                    <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-4">
        <template x-for="(question, qIndex) in questions" :key="question._uid">
            <div class="bg-white rounded border shadow-sm overflow-hidden transition-all"
                 :class="question._open ? 'border-indigo-300' : 'border-gray-200'">

                {{-- Question header / collapse bar --}}
                <div class="flex items-center justify-between px-4 py-3 cursor-pointer select-none"
                     :class="question._open ? 'bg-indigo-50 border-b border-indigo-100' : 'bg-gray-50'"
                     @click="question._open = !question._open">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-xs font-semibold text-indigo-600 shrink-0" x-text="`Q${qIndex + 1}`"></span>
                        <span class="text-xs text-gray-500 truncate max-w-xs"
                              x-text="question.body ? stripHtml(question.body).substring(0,60) || 'Untitled question' : 'Untitled question'"></span>
                        <span class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded font-medium shrink-0"
                              x-text="getTypeLabel(question.question_type_id)"></span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-2">
                        <button type="button" @click.stop="removeQuestion(qIndex)"
                                class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-0.5 rounded hover:bg-red-50 transition-colors">
                            Remove
                        </button>
                        <svg class="w-4 h-4 text-gray-400 transition-transform"
                             :class="question._open ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Question body --}}
                <div x-show="question._open"
                     x-transition:enter="transition-all duration-200 ease-out overflow-hidden"
                     x-transition:enter-start="max-h-0 opacity-0"
                     x-transition:enter-end="max-h-[2000px] opacity-100"
                     x-transition:leave="transition-all duration-150 ease-in overflow-hidden"
                     x-transition:leave-start="max-h-[2000px] opacity-100"
                     x-transition:leave-end="max-h-0 opacity-0"
                     class="p-5 space-y-5">

                    {{-- Type + Marks --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Question Type</label>
                            <select :name="`questions[${qIndex}][question_type_id]`"
                                    x-model.number="question.question_type_id"
                                    @change="onTypeChange(question)"
                                    class="w-full border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <template x-for="type in questionTypes" :key="type.id">
                                    <option :value="type.id" x-text="type.label"
                                            :selected="question.question_type_id == type.id"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Marks</label>
                            <input type="number"
                                   :name="`questions[${qIndex}][marks]`"
                                   x-model.number="question.marks"
                                   min="0" step="0.5"
                                   class="w-full border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    {{-- Question Body (Quill rich editor) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Question Body <span class="text-red-500">*</span>
                        </label>
                        {{-- Hidden input carries the HTML value for form submission --}}
                        <input type="hidden" :name="`questions[${qIndex}][body]`" :value="question.body">
                        {{-- Quill container: initialised by Alpine's x-effect when open --}}
                        <div :id="`quill-${question._uid}`"
                             class="quill-editor-container border border-gray-300 rounded bg-white text-sm"
                             style="min-height:100px;"
                             x-init="initQuill(question)"></div>
                    </div>

                    {{-- ── MEDIA (progressive disclosure) ── --}}
                    <div class="space-y-2">
                        {{-- Image toggle --}}
                        <div>
                            <button type="button" @click="question._showImage = !question._showImage"
                                    class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-indigo-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="question._showImage ? '− Hide image' : '+ Add question image'"></span>
                            </button>
                            <div x-show="question._showImage"
                                 x-transition:enter="transition-all duration-150 ease-out overflow-hidden"
                                 x-transition:enter-start="max-h-0 opacity-0"
                                 x-transition:enter-end="max-h-40 opacity-100"
                                 x-transition:leave="transition-all duration-100 ease-in overflow-hidden"
                                 x-transition:leave-start="max-h-40 opacity-100"
                                 x-transition:leave-end="max-h-0 opacity-0"
                                 class="mt-2">
                                <input type="file"
                                       :name="`questions[${qIndex}][image]`"
                                       accept="image/*"
                                       @change="handleQuestionImage($event, question)"
                                       class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <template x-if="question.imagePreview">
                                    <div class="mt-2 relative inline-block">
                                        <img :src="question.imagePreview"
                                             class="h-24 w-auto rounded object-cover border border-gray-200">
                                        <button type="button"
                                                @click="question.imagePreview = null; question._showImage = false"
                                                class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded text-xs flex items-center justify-center hover:bg-red-600">
                                            ✕
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Video toggle --}}
                        <div>
                            <button type="button" @click="question._showVideo = !question._showVideo"
                                    x-transition:enter="transition-all duration-150 ease-out overflow-hidden"
                                    x-transition:enter-start="max-h-0 opacity-0"
                                    x-transition:enter-end="max-h-40 opacity-100"
                                    x-transition:leave="transition-all duration-100 ease-in overflow-hidden"
                                    x-transition:leave-start="max-h-40 opacity-100"
                                    x-transition:leave-end="max-h-0 opacity-0"
                                    class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-indigo-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="question._showVideo ? '− Hide video' : '+ Add YouTube video'"></span>
                            </button>
                            <div x-show="question._showVideo" x-transition class="mt-2">
                                <input type="url"
                                       :name="`questions[${qIndex}][video_url]`"
                                       x-model="question.video_url"
                                       class="w-full border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2"
                                       placeholder="https://youtube.com/watch?v=...">
                            </div>
                        </div>
                    </div>

                    {{-- ── OPTIONS (binary / single_choice / multiple_choice) ── --}}
                    <div x-show="hasOptions(question.question_type_id)" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-gray-600">
                                Options
                                <span class="text-gray-400 ml-1 font-normal"
                                      x-text="isMultiple(question.question_type_id) ? '(check all correct answers)' : '(select the correct answer)'">
                                </span>
                            </label>
                            {{-- Only show "Add option" for non-binary types --}}
                            <button type="button" x-show="!isBinary(question.question_type_id)"
                                    @click="addOption(qIndex)"
                                    class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                                + Add option
                            </button>
                            {{-- Binary info badge --}}
                            <span x-show="isBinary(question.question_type_id)"
                                  class="text-xs text-gray-400 italic">Fixed Yes / No</span>
                        </div>

                        <template x-for="(option, oIndex) in question.options" :key="oIndex">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded border border-gray-200">

                                {{-- Correct indicator --}}
                                <div class="flex items-center">
                                    <template x-if="isMultiple(question.question_type_id)">
                                        <input type="checkbox"
                                               :name="`questions[${qIndex}][options][${oIndex}][is_correct]`"
                                               value="1"
                                               :checked="option.is_correct"
                                               @change="option.is_correct = $event.target.checked"
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </template>
                                    <template x-if="!isMultiple(question.question_type_id)">
                                        <input type="radio"
                                               :name="`questions[${qIndex}][correct_option]`"
                                               :value="oIndex"
                                               :checked="question.correctOptionIndex === oIndex"
                                               @change="setCorrectOption(question, oIndex)"
                                               class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </template>
                                </div>

                                <input type="hidden"
                                       :name="`questions[${qIndex}][options][${oIndex}][is_correct]`"
                                       :value="option.is_correct ? '1' : '0'">

                                <div class="flex-1 space-y-2 min-w-0">
                                    {{-- Binary: read-only label; others: editable --}}
                                    <template x-if="isBinary(question.question_type_id)">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-700" x-text="option.body"></span>
                                            <input type="hidden"
                                                   :name="`questions[${qIndex}][options][${oIndex}][body]`"
                                                   :value="option.body">
                                        </div>
                                    </template>
                                    <template x-if="!isBinary(question.question_type_id)">
                                        <input type="text"
                                               :name="`questions[${qIndex}][options][${oIndex}][body]`"
                                               x-model="option.body"
                                               class="w-full border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white px-3 py-1.5"
                                               placeholder="Option text...">
                                    </template>

                                    {{-- Option image (only for non-binary) --}}
                                    <div x-show="!isBinary(question.question_type_id)">
                                        <button type="button" @click="option._showImage = !option._showImage"
                                                class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-indigo-500 transition-colors">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span x-text="option._showImage ? 'Hide image' : 'Add image'"></span>
                                        </button>
                                        <div x-show="option._showImage"
                                             x-transition:enter="transition-all duration-150 ease-out overflow-hidden"
                                             x-transition:enter-start="max-h-0 opacity-0"
                                             x-transition:enter-end="max-h-40 opacity-100"
                                             x-transition:leave="transition-all duration-100 ease-in overflow-hidden"
                                             x-transition:leave-start="max-h-40 opacity-100"
                                             x-transition:leave-end="max-h-0 opacity-0"
                                             class="mt-1.5 flex items-center gap-2">
                                            <input type="file"
                                                   :name="`questions[${qIndex}][options][${oIndex}][image]`"
                                                   accept="image/*"
                                                   @change="handleOptionImage($event, option)"
                                                   class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-white file:text-gray-600 hover:file:bg-gray-100 file:border file:border-gray-300">
                                            <template x-if="option.imagePreview">
                                                <img :src="option.imagePreview"
                                                     class="h-8 w-auto rounded object-cover border border-gray-200">
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Remove option (not binary, must keep ≥ 2) --}}
                                <button type="button"
                                        x-show="!isBinary(question.question_type_id) && question.options.length > 2"
                                        @click="removeOption(qIndex, oIndex)"
                                        class="text-gray-400 hover:text-red-500 transition-colors pt-0.5 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- ── ANSWER KEY (number_input / text_input) ── --}}
                    <div x-show="!hasOptions(question.question_type_id)" class="space-y-2">
                        <label class="text-xs font-medium text-gray-600">Answer Key</label>

                        {{-- Number input --}}
                        <template x-if="getTypeSlug(question.question_type_id) === 'number_input'">
                            <div class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded border border-gray-200">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Correct Value <span
                                            class="text-red-400">*</span></label>
                                    <input type="number" step="any"
                                           :name="`questions[${qIndex}][answer_key][correct_number_value]`"
                                           x-model.number="question.answer_key.correct_number_value"
                                           class="w-full border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white px-3 py-1.5">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1 flex items-center gap-1">
                                        Tolerance ±
                                        {{-- Tooltip --}}
                                        <span class="relative group cursor-help">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 w-52 bg-gray-800 text-white text-xs rounded px-2.5 py-1.5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 leading-relaxed">
                                                Allow answers within ± this value of the correct answer. E.g. correct=10, tolerance=0.5 accepts 9.5–10.5.
                                                <span
                                                    class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></span>
                                            </span>
                                        </span>
                                    </label>
                                    <input type="number" step="any" min="0"
                                           :name="`questions[${qIndex}][answer_key][number_tolerance]`"
                                           x-model.number="question.answer_key.number_tolerance"
                                           class="w-full border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white px-3 py-1.5">
                                </div>
                            </div>
                        </template>

                        {{-- Text input --}}
                        <template x-if="getTypeSlug(question.question_type_id) === 'text_input'">
                            <div class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded border border-gray-200">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Correct Answer <span
                                            class="text-red-400">*</span></label>
                                    <input type="text"
                                           :name="`questions[${qIndex}][answer_key][correct_text_value]`"
                                           x-model="question.answer_key.correct_text_value"
                                           class="w-full border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white px-3 py-1.5"
                                           placeholder="Expected answer">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1 flex items-center gap-1">
                                        Match Strategy
                                        <span class="relative group cursor-help">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 w-60 bg-gray-800 text-white text-xs rounded px-2.5 py-1.5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 leading-relaxed">
                                                <strong>Case insensitive:</strong> "Paris" = "paris"<br>
                                                <strong>Exact:</strong> must match perfectly<br>
                                                <strong>Contains:</strong> answer just needs to include the key phrase
                                                <span
                                                    class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></span>
                                            </span>
                                        </span>
                                    </label>
                                    <select :name="`questions[${qIndex}][answer_key][match_strategy]`"
                                            x-model="question.answer_key.match_strategy"
                                            class="w-full border-gray-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white px-3 py-1.5">
                                        <option value="case_insensitive">Case insensitive</option>
                                        <option value="exact">Exact match</option>
                                        <option value="contains">Contains</option>
                                    </select>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>{{-- /question._open --}}
            </div>{{-- /question card --}}
        </template>
    </div>{{-- /questions --}}

    {{-- ── ADD QUESTION + SUBMIT ─────────────────────────────────────── --}}
    <div class="mt-5 flex items-center justify-between gap-4">
        <button type="button" @click="addQuestion()"
                class="inline-flex items-center gap-2 border border-dashed border-indigo-400 text-indigo-600 hover:bg-indigo-50 text-sm font-medium px-4 py-2 rounded transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Question
        </button>

        <div class="flex items-center gap-3">
            {{-- Question count guard hint --}}
            <span x-show="questions.length === 0" class="text-xs text-amber-600 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Add at least one question
            </span>
            <a href="{{ route('quizzes.index') }}"
               class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    :disabled="questions.length === 0"
                    :class="questions.length === 0 ? 'opacity-50 cursor-not-allowed bg-indigo-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                    class="px-5 py-2 text-white text-sm font-medium rounded transition-colors">
                Save Quiz
            </button>
        </div>
    </div>

</div>{{-- /x-data --}}

{{-- Quill JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>

@push('scripts')
    <script>
        // Store Quill instances outside Alpine to avoid proxy issues
        const _quillInstances = {};

        function quizBuilder() {
            const INITIAL = {!! $safeInitial !!};
            const TYPES = {!! $safeTypes !!};

            const emptyAnswerKey = () => ({
                correct_text_value: '',
                correct_number_value: '',
                number_tolerance: 0,
                match_strategy: 'case_insensitive',
            });

            const binaryOptions = () => [
                {body: 'Yes', is_correct: true, imagePreview: null, _showImage: false},
                {body: 'No', is_correct: false, imagePreview: null, _showImage: false},
            ];

            const defaultOptions = (markFirstCorrect = false) => [
                {body: '', is_correct: markFirstCorrect, imagePreview: null, _showImage: false},
                {body: '', is_correct: false, imagePreview: null, _showImage: false},
            ];

            let _uidCounter = 1;
            const uid = () => `q${_uidCounter++}`;

            const makeQuestion = (overrides = {}) => {
                const typeId = overrides.question_type_id ?? 2;
                const type = TYPES.find(t => t.id == typeId) ?? {};
                const isBin = type.slug === 'binary';
                const hasOpts = type.has_options ?? false;
                const allowsMultiple = type.allows_multiple_correct ?? false;

                let opts = [];
                if (isBin) {
                    opts = binaryOptions();
                } else if (hasOpts) {
                    opts = defaultOptions(!allowsMultiple);
                }

                return {
                    _uid: uid(),
                    _open: true,
                    _showImage: false,
                    _showVideo: false,
                    question_type_id: typeId,
                    body: '',
                    marks: 1,
                    video_url: '',
                    imagePreview: null,
                    correctOptionIndex: 0,
                    options: opts,
                    answer_key: emptyAnswerKey(),
                    ...overrides,
                };
            };

            // Hydrate from server (edit page / validation flash)
            const hydrateQuestions = (raw) => {
                if (!Array.isArray(raw)) return [];
                return raw.map(q => {
                    const type = TYPES.find(t => t.id == q.question_type_id) ?? {};
                    const isBin = type.slug === 'binary';
                    const allowsMultiple = type.allows_multiple_correct ?? false;

                    const opts = isBin
                        ? binaryOptions()
                        : (q.options ?? []).map(o => ({
                            body: o.body ?? '',
                            is_correct: !!o.is_correct,
                            imagePreview: o.existing_image ?? null,
                            _showImage: !!(o.existing_image),
                        }));

                    // For single choice: find correct index and reset all others to false
                    let correctIdx = 0;
                    if (!isBin && type.has_options && !allowsMultiple) {
                        correctIdx = opts.findIndex(o => o.is_correct);
                        if (correctIdx === -1) correctIdx = 0;
                        // Enforce only one correct
                        opts.forEach((o, i) => {
                            o.is_correct = i === correctIdx;
                        });
                    }

                    return {
                        _uid: uid(),
                        _open: true,
                        _showImage: !!(q.existing_image),
                        _showVideo: !!(q.video_url),
                        question_type_id: q.question_type_id ?? 2,
                        body: q.body ?? '',
                        marks: q.marks ?? 1,
                        video_url: q.video_url ?? '',
                        imagePreview: q.existing_image ?? null,
                        correctOptionIndex: correctIdx,
                        options: opts,
                        answer_key: {
                            correct_text_value: q.answer_key?.correct_text_value ?? '',
                            correct_number_value: q.answer_key?.correct_number_value ?? '',
                            number_tolerance: q.answer_key?.number_tolerance ?? 0,
                            match_strategy: q.answer_key?.match_strategy ?? 'case_insensitive',
                        },
                    };
                });
            };

            return {
                title: INITIAL?.title ?? '',
                description: INITIAL?.description ?? '',
                questions: INITIAL?.questions ? hydrateQuestions(INITIAL.questions) : [],
                questionTypes: TYPES,

                init() {
                    // If no questions yet, add one blank by default for better UX
                    if (this.questions.length === 0) this.addQuestion();
                },

                // ── Quill ──────────────────────────────────────────────────────
                initQuill(question) {
                    // Defer so DOM element exists
                    this.$nextTick(() => {
                        const el = document.getElementById(`quill-${question._uid}`);
                        if (!el || _quillInstances[question._uid]) return;

                        const quill = new Quill(el, {
                            theme: 'snow',
                            placeholder: 'Type your question here. Bold, italic, lists supported...',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline'],
                                    [{list: 'ordered'}, {list: 'bullet'}],
                                    ['clean'],
                                ],
                            },
                        });

                        // Set initial content
                        if (question.body) {
                            quill.root.innerHTML = question.body;
                        }

                        // Sync to Alpine on change
                        quill.on('text-change', () => {
                            const html = quill.root.innerHTML;
                            question.body = html === '<p><br></p>' ? '' : html;
                        });

                        _quillInstances[question._uid] = quill;
                    });
                },

                stripHtml(html) {
                    const tmp = document.createElement('div');
                    tmp.innerHTML = html ?? '';
                    return tmp.textContent || tmp.innerText || '';
                },

                // ── Helpers ────────────────────────────────────────────────────
                getType(typeId) {
                    return this.questionTypes.find(t => t.id == typeId) ?? {};
                },
                getTypeSlug(typeId) {
                    return this.getType(typeId).slug ?? '';
                },
                getTypeLabel(typeId) {
                    return this.getType(typeId).label ?? '';
                },
                hasOptions(typeId) {
                    return this.getType(typeId).has_options ?? false;
                },
                isMultiple(typeId) {
                    return this.getType(typeId).allows_multiple_correct ?? false;
                },
                isBinary(typeId) {
                    return this.getType(typeId).slug === 'binary';
                },
                totalMarks() {
                    return this.questions.reduce((sum, q) => sum + (parseFloat(q.marks) || 0), 0);
                },

                // ── Question mutations ─────────────────────────────────────────
                onTypeChange(question) {
                    const slug = this.getTypeSlug(question.question_type_id);
                    const type = this.getType(question.question_type_id);

                    if (slug === 'binary') {
                        question.options = binaryOptions();
                        question.correctOptionIndex = 0;
                    } else if (type.has_options) {
                        question.options = defaultOptions(!type.allows_multiple_correct);
                        question.correctOptionIndex = 0;
                    } else {
                        question.options = [];
                    }
                },

                addQuestion() {
                    this.questions.push(makeQuestion());
                },

                removeQuestion(i) {
                    const q = this.questions[i];
                    if (_quillInstances[q._uid]) {
                        delete _quillInstances[q._uid];
                    }
                    this.questions.splice(i, 1);
                },

                // ── Option mutations ───────────────────────────────────────────
                addOption(i) {
                    this.questions[i].options.push({
                        body: '',
                        is_correct: false,
                        imagePreview: null,
                        _showImage: false
                    });
                },

                removeOption(i, j) {
                    this.questions[i].options.splice(j, 1);
                },

                setCorrectOption(question, oIndex) {
                    question.correctOptionIndex = oIndex;
                    question.options.forEach((o, idx) => {
                        o.is_correct = idx === oIndex;
                    });
                },

                // ── Media ──────────────────────────────────────────────────────
                handleQuestionImage(event, question) {
                    const file = event.target.files[0];
                    if (file) question.imagePreview = URL.createObjectURL(file);
                },
                handleOptionImage(event, option) {
                    const file = event.target.files[0];
                    if (file) option.imagePreview = URL.createObjectURL(file);
                },
            };
        }
    </script>
@endpush
