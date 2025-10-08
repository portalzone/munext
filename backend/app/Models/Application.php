<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'student_id',
        'cover_letter',
        'resume_path',
        'status',
        'screening_answers',
        'applied_at',
        'reviewed_at',
        'notes',
    ];

    protected $casts = [
        'screening_answers' => 'array',
        'applied_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function studentProfile()
    {
        return $this->hasOneThrough(
            StudentProfile::class,
            User::class,
            'id',
            'user_id',
            'student_id',
            'id'
        );
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', 'shortlisted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('applied_at', 'desc');
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
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isReviewed()
    {
        return in_array($this->status, ['reviewed', 'shortlisted', 'rejected']);
    }

    public function markAsReviewed()
    {
        $this->update([
            'status' => 'reviewed',
            'reviewed_at' => now(),
        ]);
    }

    public function markAsShortlisted()
    {
        $this->update([
            'status' => 'shortlisted',
            'reviewed_at' => now(),
        ]);
    }

    public function markAsRejected()
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
        ]);
    }
}
