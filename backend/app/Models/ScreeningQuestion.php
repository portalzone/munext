<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'question',
        'question_type',
        'is_required',
        'options',
        'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'order' => 'integer',
    ];

    // Relationships
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    // Helper methods
    public function isTextType()
    {
        return $this->question_type === 'text';
    }

    public function isMultipleChoice()
    {
        return $this->question_type === 'multiple_choice';
    }

    public function isYesNo()
    {
        return $this->question_type === 'yes_no';
    }
}
