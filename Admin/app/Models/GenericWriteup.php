<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenericWriteup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'year',
        'college_id',
        'title',
        'content',
        'created_by',
        'updated_by',
        'is_active',
    ];


    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForCollege($query, $collegeId)
    {
        return $query->where('college_id', $collegeId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}