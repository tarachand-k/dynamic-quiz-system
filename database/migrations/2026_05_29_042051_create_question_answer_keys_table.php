<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('question_answer_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('correct_text_value')->nullable();
            $table->decimal('correct_number_value', 15, 4)->nullable();
            $table->decimal('number_tolerance', 10, 4)->nullable()->default(0);
            $table->enum('match_strategy', ['exact', 'case_insensitive', 'contains'])
                ->default('case_insensitive');

            $table->unique('question_id'); // one key per question
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_answer_keys');
    }
};
