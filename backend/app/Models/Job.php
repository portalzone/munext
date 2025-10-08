<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'title',
        'description',
        'requirements',
        'responsibilities',
        'job_type',
        'location',
        'salary_min',
        'salary_max',
        'salary_period',
        'experience_level',
        'category',
        'skills_required',
        'benefits',
        'application_deadline',
        'start_date',
        'is_remote',
        'status',
        'views_count',
    ];

    protected $casts = [
        'skills_required' => 'array',
        'benefits' => 'array',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'application_deadline' => 'date',
        'start_date' => 'date',
        'is_remote' => 'boolean',
        'views_count' => 'integer',
    ];

    // Relationships
    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function employerProfile()
    {
        return $this->hasOneThrough(
            EmployerProfile::class,
            User::class,
            'id',
            'user_id',
            'employer_id',
            'id'
        );
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function screeningQuestions()
    {
        return $this->hasMany(ScreeningQuestion::class);
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_jobs', 'job_id', 'user_id')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
                    ->where(function($q) {
                        $q->whereNull('application_deadline')
                          ->orWhere('application_deadline', '>=', now());
                    });
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByJobType($query, $type)
    {
        return $query->where('job_type', $type);
    }

    public function scopeRemote($query)
    {
        return $query->where('is_remote', true);
    }

    // Helper methods
    public function isOpen()
    {
        if ($this->status !== 'open') {
            return false;
        }
        if ($this->application_deadline) {
            return $this->application_deadline >= now();
        }
        return true;
    }

    public function applicationsCount()
    {
        return $this->applications()->count();
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function getSalaryRangeAttribute()
    {
        if ($this->salary_min && $this->salary_max) {
            return '$' . number_format($this->salary_min) . ' - $' . number_format($this->salary_max) . ' ' . $this->salary_period;
        }
        return 'Not specified';
    }

    public function hasDeadlinePassed()
    {
        if (!$this->application_deadline) {
            return false;
        }
        return $this->application_deadline < now();
    }
}
