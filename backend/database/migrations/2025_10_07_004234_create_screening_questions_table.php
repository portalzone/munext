<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->text('question');
            $table->enum('question_type', ['text', 'multiple_choice', 'yes_no'])->default('text');
            $table->boolean('is_required')->default(true);
            $table->json('options')->nullable(); // For multiple choice questions
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_questions');
    }
};
