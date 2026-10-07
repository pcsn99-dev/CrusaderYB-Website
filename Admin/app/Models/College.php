<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class College extends Model
{
    protected $table = 'colleges';

    public $timestamps = false;

    protected $fillable = [
        'college_name',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function students(): HasMany
    {
        return $this->hasMany(StudentInfo::class, 'college_id');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(
            Program::class,
            'college_program',
            'college_id',
            'program_id'
        );
    }

    public function writeups(): HasManyThrough
    {
        return $this->hasManyThrough(
            Writeup::class,
            StudentInfo::class,
            'college_id',
            'student_info_id',
            'id',
            'id'
        );
    }

    public function pictorials(): HasMany
    {
        return $this->hasMany(Pictorial::class, 'college_id');
    }

    public function delayedPictorials(): BelongsToMany
    {
        return $this->belongsToMany(
            Pictorial::class,
            'pictorial_colleges',
            'college_id',
            'pictorial_id'
        )->withTimestamps();
    }
}