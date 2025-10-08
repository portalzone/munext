<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_verified',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'password' => 'hashed',
    ];

    // Relationships
    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function employerProfile()
    {
        return $this->hasOne(EmployerProfile::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'employer_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'student_id');
    }

    public function savedJobs()
    {
        return $this->belongsToMany(Job::class, 'saved_jobs', 'user_id', 'job_id')
                    ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Helper methods
    public function isStudent()
    {
        return in_array($this->role, ['student', 'alumni']);
    }

    public function isEmployer()
    {
        return $this->role === 'employer';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function hasProfile()
    {
        if ($this->isStudent()) {
            return $this->studentProfile()->exists();
        }
        if ($this->isEmployer()) {
            return $this->employerProfile()->exists();
        }
        return false;
    }
}
