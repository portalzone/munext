<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->text('cover_letter');
            $table->string('resume_path')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'shortlisted', 'rejected'])->default('pending');
            $table->json('screening_answers')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable(); // Employer notes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
