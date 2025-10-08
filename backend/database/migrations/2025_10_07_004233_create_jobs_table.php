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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();
            $table->enum('job_type', ['full-time', 'part-time', 'contract', 'internship', 'co-op']);
            $table->string('location');
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('salary_period')->default('per year');
            $table->enum('experience_level', ['entry', 'intermediate', 'senior', 'executive']);
            $table->string('category');
            $table->json('skills_required')->nullable();
            $table->json('benefits')->nullable();
            $table->date('application_deadline')->nullable();
            $table->date('start_date')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->enum('status', ['open', 'closed', 'filled'])->default('open');
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
