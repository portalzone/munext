<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_description',
        'industry',
        'company_size',
        'logo_path',
        'website',
        'contact_person',
        'contact_email',
        'contact_phone',
        'location',
        'founded_year',
    ];

    protected $casts = [
        'founded_year' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'employer_id', 'user_id');
    }

    // Accessors
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return url('storage/' . $this->logo_path);
        }
        return null;
    }

    // Helper methods
    public function hasLogo()
    {
        return !empty($this->logo_path);
    }

    public function activeJobsCount()
    {
        return $this->jobs()->where('status', 'open')->count();
    }
}
