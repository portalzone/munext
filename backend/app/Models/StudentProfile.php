<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_number',
        'program',
        'faculty',
        'graduation_year',
        'gpa',
        'bio',
        'skills',
        'resume_path',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'phone',
        'location',
        'available_from',
        'work_authorization',
    ];

    protected $casts = [
        'skills' => 'array',
        'graduation_year' => 'integer',
        'gpa' => 'decimal:2',
        'available_from' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getResumeUrlAttribute()
    {
        if ($this->resume_path) {
            return url('storage/' . $this->resume_path);
        }
        return null;
    }

    // Helper methods
    public function isAlumni()
    {
        return $this->user->role === 'alumni';
    }

    public function hasResume()
    {
        return !empty($this->resume_path);
    }
}
