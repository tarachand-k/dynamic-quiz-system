<?php

namespace App\Evaluators;

use InvalidArgumentException;

class QuestionEvaluatorFactory
{
    private static array $map = [
        'binary' => ChoiceEvaluator::class,
        'single_choice' => ChoiceEvaluator::class,
        'multiple_choice' => MultipleChoiceEvaluator::class,
        'number_input' => NumberInputEvaluator::class,
        'text_input' => TextInputEvaluator::class,
    ];

    public static function make(string $slug): QuestionEvaluatorInterface
    {
        $class = self::$map[$slug]
            ?? throw new InvalidArgumentException("No evaluator for question type: {$slug}");

        return new $class();
    }


    public static function register(string $slug, string $evaluatorClass): void
    {
        self::$map[$slug] = $evaluatorClass;
    }
}
