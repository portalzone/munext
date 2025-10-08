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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('student_number')->nullable();
            $table->string('program');
            $table->string('faculty');
            $table->integer('graduation_year');
            $table->decimal('gpa', 3, 2)->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->date('available_from')->nullable();
            $table->string('work_authorization')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
