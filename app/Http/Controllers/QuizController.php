<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function __construct(private readonly QuizService $quizService)
    {
    }

    public function index(): View
    {
        $quizzes = auth()->user()
            ->quizzes()
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->paginate(15);

        return view('quizzes.index', compact('quizzes'));
    }

    public function create(): View
    {
        return view('quizzes.create');
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $quiz = $this->quizService->create($request->validated(), auth()->id());

        return redirect()->route('quizzes.show', $quiz)
            ->with('success', 'Quiz created successfully.');
    }

    public function show(Quiz $quiz): View
    {
        $quiz->load(['questions.questionType', 'questions.options', 'questions.media', 'questions.answerKey']);

        return view('quizzes.show', compact('quiz'));
    }

    public function edit(Quiz $quiz): View
    {
        $quiz->load(['questions.questionType', 'questions.options', 'questions.answerKey', 'questions.media']);

        return view('quizzes.edit', compact('quiz'));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->quizService->update($quiz, $request->validated());

        return redirect()->route('quizzes.index')
            ->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->quizService->delete($quiz);

        return redirect()->route('quizzes.index')
            ->with('success', 'Quiz deleted.');
    }

    public function stats(Quiz $quiz): View
    {
        $quiz->load([
            'questions.questionType',
            'attempts.answers',
        ]);

        $attempts = $quiz->attempts;

        // Per question accuracy
        $questionStats = $quiz->questions->map(function ($question) use ($attempts) {
            $total = $attempts->count();
            $correct = 0;

            if ($total > 0) {
                foreach ($attempts as $attempt) {
                    $answer = $attempt->answers->firstWhere('question_id', $question->id);
                    if ($answer && $answer->is_correct) {
                        $correct++;
                    }
                }
            }

            return [
                'question' => $question->body,
                'type' => $question->questionType->label ?? '',
                'total' => $total,
                'correct' => $correct,
                'accuracy' => $total > 0 ? round(($correct / $total) * 100) : 0,
            ];
        });

        return view('quizzes.stats', compact('quiz', 'attempts', 'questionStats'));
    }
}
