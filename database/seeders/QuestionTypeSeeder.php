<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['id' => 1, 'slug' => 'binary', 'label' => 'Binary (Yes / No)', 'has_options' => true, 'allows_multiple_correct' => false],
            ['id' => 2, 'slug' => 'single_choice', 'label' => 'Single Choice', 'has_options' => true, 'allows_multiple_correct' => false],
            ['id' => 3, 'slug' => 'multiple_choice', 'label' => 'Multiple Choice', 'has_options' => true, 'allows_multiple_correct' => true],
            ['id' => 4, 'slug' => 'number_input', 'label' => 'Number Input', 'has_options' => false, 'allows_multiple_correct' => false],
            ['id' => 5, 'slug' => 'text_input', 'label' => 'Text Input', 'has_options' => false, 'allows_multiple_correct' => false],
        ];

        DB::table('question_types')->insertOrIgnore($types);
    }
}
