# AI Usage

I used Claude (claude.ai) as the primary AI tool throughout this project.

## Summary

AI was used for the majority of code generation. My contribution was in the architecture decisions made before
prompting, corrections made during review, and all debugging — every bug in this project was found through testing and
inspecting payloads, not flagged by the AI. Claude wrote to a spec I defined and corrected to standards I enforced.

## Prompts and What They Produced

### 1. Schema Design

**Prompt:**
> Act as a Senior Database Architect. Analyze the following project requirements:
>
> [requirements pasted]
>
> 1. Identify Entities: List the core operational objects that need tracking.
> 2. Schema Design: Propose an optimized relational database schema (normalized to 3NF).
> 3. Table Details: For each table, provide column names with specific data types
     >    (e.g., BIGINT/UUID, VARCHAR(255), TIMESTAMP), primary and foreign keys clearly
     >    identified, and constraints (e.g., NOT NULL, UNIQUE).
> 4. Relationships: Explain the cardinality (1:1, 1:N, N:M) between tables and justify
     >    junction tables where needed.
> 5. Output Format: Provide the schema in both a bulleted list and Mermaid.js code
     >    for visualization.

Claude produced a reasonable first draft. I made two corrections before moving forward:

- It included a separate `option_media` table. I pushed back — a `media_url` column
  directly on `options` is simpler and sufficient since each option has at most one image.
- It missed `match_strategy` on `question_answer_keys`. I added this requirement myself
  after thinking through how text answer comparison would need to work.

**Follow-up prompt:**
> Update the schema with these fixes:
> 1. Remove the `option_media` table, add `media_url VARCHAR(2048) NULLABLE` to `options`
> 2. `options` must have either `body` or `media_url` — add a check constraint
> 3. Add `match_strategy ENUM('exact', 'case_insensitive', 'contains') NOT NULL
>    DEFAULT 'case_insensitive'` to `question_answer_keys`
     > Keep everything else as-is.
>
> Also add minimal seed/example inserts covering one quiz with all 5 question types,
> correct options and answer keys set, and one completed attempt with all answer types.

---

### 2. Backend Implementation

**Prompt:**
> Based on our agreed schema, implement everything needed in the backend. Provide all
> migrations, models, services, form request validation, and any other required
> components. Make sure everything follows Laravel 12/13 conventions. Provide the
> response as in-chat markdown.

Output covered migrations, Eloquent models, `QuizService`, `AttemptService`, and form
requests. I reviewed everything and made the following corrections:

- `AttemptService` iterated over submitted answers instead of quiz questions. This meant skipped questions left no
  answer record and were invisible in results and stats. I
  identified this through testing and directed the fix to iterate over quiz questions.
- `QuizService::update` and `delete` called `deleteQuestionMedia` without eager loading
  `media` and `options` first. I caught this during code review and added the eager loads.
- The show page referenced a `status` column in a query that does not exist in the schema. Caught during testing and
  corrected to use `whereNotNull('submitted_at')`.

---

### 3. Routing and Auth Setup

**Prompt:**
> I replaced the entire web.php with your code and tested the project. When I visit
> http://127.0.0.1:8000/ I get redirected to the login page. The login page has no
> button to go to the register page so I manually changed the URL. After registering
> and logging in I get: Route [dashboard] not defined.
>
> We should fix this — either show a welcome page with login and signup links or fix
> it another way with minimal changes. Should we use the dashboard pages from Breeze
> or skip them entirely?

Claude suggested wiring the root route and post-login redirect directly to
`quizzes.index` and removing the dashboard entirely. I agreed — a separate dashboard
page added no value. Registration was also disabled since the system is single-user.

---

### 4. Evaluation Logic

**Prompt:**
> Implement the evaluation logic. The system must be extensible — adding a new question
> type in the future should not require changes in multiple places.

I specifically asked for the interface and factory pattern because extensibility is a
core requirement. Claude produced `QuestionEvaluatorInterface`, four evaluator classes,
and `QuestionEvaluatorFactory`. I reviewed each evaluator:

- `ChoiceEvaluator` — correct, checks exactly one selected option is marked correct
- `MultipleChoiceEvaluator` — correct, sorts both ID arrays before comparing
- `NumberInputEvaluator` — correct, applies tolerance using `abs()`
- `TextInputEvaluator` — correct, three strategies handled via `match` expression

No corrections needed here.

---

### 5. Frontend — Blade Views

**Prompt:**
> Let's build all the Blade views. The design should be consistent and clean. I have
> provided a screenshot of the current UI — the navbar and header are taking too much
> space. Move the navigation to a left sidebar styled like claude.ai and show the header
> at the top with less space. Use Alpine.js for the quiz builder and Quill.js for the
> rich text question editor. Also:
>
> 1. Form data should persist after a validation failure and errors should show inline
     >    next to the relevant field, not all at the top.
> 2. The edit page should pre-fill all existing quiz data.
> 3. Binary questions should be locked to exactly two options (Yes/No). Should we allow
     >    image upload per option for binary?
> 4. Only show the image upload and YouTube URL fields when the user wants to fill them,
     >    to save space. Do the same for option image uploads.
> 5. Add an info tooltip for fields like tolerance on number input so users understand
     >    what it does.
> 6. Do not allow saving a quiz without at least one question.
> 7. What about a multi-step form with each question on a new step — is that better UX
     >    than the current single-page approach?

I have experience React and Blade but limited hands-on experience with Alpine.js. I used Claude to generate the views
and the Alpine quiz builder component, then tested everything end to end myself.

Several bugs surfaced during testing that I identified and directed fixes for:

**Bug 1 — Single choice options all submitting as incorrect:**
Inspecting the debug payload showed all options with `is_correct: 0` even when a radio button appeared selected. I
traced this to `makeQuestion()` initializing all options with `is_correct: false` and nothing calling `setCorrectOption`
on initialization. The fix was to mark the first option correct by default for single-choice types in `makeQuestion()`.

**Bug 2 — Edit page sending all options as correct for single choice:**
The submitted payload showed all four options with `is_correct: 1`. I traced it to `hydrateQuestions()` loading
`is_correct` values from the database correctly but never enforcing single-correct logic before the form rendered. The
fix was to loop through options during hydration and reset all except the correct index to false.

**Bug 3 — Number and text input types submitting empty option arrays:**
The payload for number and text questions included `options: [{is_correct: 0, body: null}]`. `makeQuestion()` always
called `defaultOptions()` regardless of question type. The fix was to return an empty array for types where
`has_options` is false.

I understand what the Alpine component does and can explain every part of it. I would not have written it from scratch
at the same speed without assistance given my current Alpine experience.

---

### 6. Debugging and Fixes

Several issues were found independently during testing and then fixed with Claude's help:

| Bug                                         | How I Found It                       | Fix                                                             |
|---------------------------------------------|--------------------------------------|-----------------------------------------------------------------|
| `imageMedia` / `videoMedia` not resolving   | Images not rendering in attempt view | Added accessors to `Question` model using `media->firstWhere()` |
| Unanswered questions not recorded           | Reviewing `AttemptService` logic     | Iterate questions not submitted answers                         |
| Edit page images lost on validation failure | Manual testing                       | Added user-facing warning to re-upload                          |
| `status` column reference                   | Runtime error during testing         | Changed to `whereNotNull('submitted_at')`                       |
| Debug log left in `StoreQuizRequest`        | Code review                          | Removed `Log::debug` call                                       |
| Single choice validation failing on create  | Debug payload inspection             | Fixed `makeQuestion()` initialization                           |
| All options correct on edit submit          | Debug payload inspection             | Fixed `hydrateQuestions()` enforcement                          |

---
