<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkWriteupBatch extends Model
{
    protected $fillable = [
        'year',
        'college_id',
        'created_by',
        'created_count',
        'undone_by',
        'undone_count',
        'undone_at',
    ];

    protected $casts = [
        'created_count' => 'integer',
        'undone_count' => 'integer',
        'undone_at' => 'datetime',
    ];

    public function college()
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function undoneBy()
    {
        return $this->belongsTo(User::class, 'undone_by');
    }

    public function writeups()
    {
        return $this->hasMany(Writeup::class, 'bulk_writeup_batch_id');
    }

  
    public function writeupsWithTrashed()
    {
        return $this->hasMany(Writeup::class, 'bulk_writeup_batch_id')
            ->withTrashed();
    }

    public function isUndone(): bool
    {
        return $this->undone_at !== null;
    }

    public function canUndo(): bool
    {
        return ! $this->isUndone()
            && $this->writeups()->exists();
    }
}