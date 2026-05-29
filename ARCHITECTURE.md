# Architecture

## Overview

Standard Laravel MVC with a service layer. The main design goal was to keep question-type-specific logic in one place so
the system can support new types without touching existing code.

## Database Schema

```
quizzes
  └── questions
        ├── question_types        (seeded lookup — drives validation and rendering)
        ├── options               (text, image url, or both — flagged is_correct)
        ├── question_media        (one image and one video max, enforced by unique constraint)
        └── question_answer_keys  (for text and number input types)

attempts
  └── answers
        └── answer_options        (pivot — which options a user selected)
```

`question_types` has two boolean flags:

- `has_options` — whether the type uses selectable options
- `allows_multiple_correct` — whether multiple options can be marked correct

These flags are used in validation, the quiz builder frontend, and evaluation without hardcoding type names anywhere
outside the factory map.

## Evaluation

Each question type has an evaluator class implementing `QuestionEvaluatorInterface`:

```
QuestionEvaluatorInterface
    ├── ChoiceEvaluator           binary, single_choice
    ├── MultipleChoiceEvaluator   multiple_choice
    ├── NumberInputEvaluator      supports tolerance range
    └── TextInputEvaluator        exact / case_insensitive / contains
```

`QuestionEvaluatorFactory` maps type slugs to evaluator classes:

```php
private static array $map = [
    'binary'          => ChoiceEvaluator::class,
    'single_choice'   => ChoiceEvaluator::class,
    'multiple_choice' => MultipleChoiceEvaluator::class,
    'number_input'    => NumberInputEvaluator::class,
    'text_input'      => TextInputEvaluator::class,
];
```

Adding a new question type means adding one evaluator class and one line in this map. The rest of the system picks it up
automatically.

## Services

**`QuizService`** handles quiz creation, update, and deletion. On update it deletes and recreates questions rather than
diffing — simpler and reliable given the complexity of media and options. Media files are cleaned up before deletion.

**`AttemptService`** iterates over quiz questions (not submitted answers) to ensure every question gets an answer record
even if skipped. Evaluation runs immediately after submission inside a single DB transaction.

## Frontend

The quiz builder is an Alpine.js component that manages all question state on the client. Questions are collapsible
cards. Each question type renders the appropriate input UI based on the `question_types` flags passed from the server.

Quill.js handles rich text editing for question bodies. The HTML output is stored directly and rendered with `{!! !!}`
on the attempt and result pages.

The attempt form is plain Blade with server-side validation enforcing that all questions are answered before the form
can be submitted.

## Auth

Laravel Breeze provides the authentication layer. Registration is disabled — the system is intended for a single quiz
creator. Attempt and result pages are public so quizzes can be shared via direct link.
