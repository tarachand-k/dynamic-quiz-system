<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitQuizRequest;
use App\Models\Attempt;
use App\Models\Quiz;
use App\Services\AttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttemptController extends Controller
{
    public function __construct(private readonly AttemptService $attemptService)
    {
    }

    public function show(Quiz $quiz): View
    {
        $quiz->load([
            'questions.questionType',
            'questions.options',
            'questions.media',
        ]);
        return view('attempts.show', compact('quiz'));
    }

    public function submit(SubmitQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $attempt = $this->attemptService->submit(
            $quiz,
            auth()->id() ?? 0,
            $request->input('answers', [])
        );
        return redirect()->route('attempts.result', $attempt);
    }

    public function result(Attempt $attempt): View
    {
        $attempt->load([
            'quiz.questions',
            'answers.question.questionType',
            'answers.question.options',
            'answers.question.answerKey',
            'answers.selectedOptions',
        ]);

        return view('attempts.result', compact('attempt'));
    }
}
