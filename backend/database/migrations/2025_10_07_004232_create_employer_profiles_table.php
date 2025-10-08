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
        Schema::create('employer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->text('company_description');
            $table->string('industry');
            $table->string('company_size')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_person');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->string('location');
            $table->integer('founded_year')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employer_profiles');
    }
};
