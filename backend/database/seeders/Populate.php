<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\EmployerProfile;
use App\Models\Job;
use Illuminate\Support\Facades\Hash;

class Populate extends Seeder
{
    public function run(): void
    {
        // Create sample students
        for ($i = 1; $i <= 5; $i++) {
            $student = User::create([
                'name' => "Student {$i}",
                'email' => "student{$i}@mun.ca",
                'password' => Hash::make('password123'),
                'role' => 'student',
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            StudentProfile::create([
                'user_id' => $student->id,
                'student_number' => '202012' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'program' => 'Computer Science',
                'faculty' => 'Faculty of Science',
                'graduation_year' => 2025,
                'gpa' => rand(30, 40) / 10,
                'bio' => 'Passionate student looking for opportunities.',
                'skills' => ['PHP', 'JavaScript', 'Python', 'React'],
                'location' => "St. John's, NL",
            ]);
        }

        // Create sample employers
        for ($i = 1; $i <= 3; $i++) {
            $employer = User::create([
                'name' => "Employer {$i}",
                'email' => "employer{$i}@company.com",
                'password' => Hash::make('password123'),
                'role' => 'employer',
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            EmployerProfile::create([
                'user_id' => $employer->id,
                'company_name' => "Tech Company {$i}",
                'company_description' => "A leading technology company specializing in innovative solutions.",
                'industry' => 'Technology',
                'company_size' => '51-200 employees',
                'website' => "https://techcompany{$i}.com",
                'contact_person' => "HR Manager {$i}",
                'contact_email' => "hr{$i}@company.com",
                'location' => "St. John's, NL",
            ]);

            // Create jobs for each employer
            Job::create([
                'employer_id' => $employer->id,
                'title' => 'Software Developer',
                'description' => 'We are looking for a talented software developer...',
                'requirements' => 'Bachelor degree in Computer Science or related field',
                'responsibilities' => 'Develop and maintain software applications',
                'job_type' => 'full-time',
                'location' => "St. John's, NL",
                'salary_min' => 50000,
                'salary_max' => 70000,
                'salary_period' => 'per year',
                'experience_level' => 'entry',
                'category' => 'Technology',
                'skills_required' => ['PHP', 'Laravel', 'Vue.js'],
                'benefits' => ['Health Insurance', 'Flexible Hours'],
                'is_remote' => false,
                'status' => 'open',
            ]);
        }

        $this->command->info('Sample data created successfully!');
    }
}
