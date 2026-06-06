<?php

namespace App\Models;

// use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Writeup extends Model
{

    use SoftDeletes;



    protected $table = 'writeups';

    protected $fillable = [
        'student_info_id',
        'writeup',
        'edited_writeup',
        'proofreader_id',
        'is_done',
        'review_status',
        'locked_by',
        'locked_at',
        'is_flagged',
        'flag_reason',
        'flagged_by',
        'flagged_at',
        'date_of_proofread',
        'reviewed_at',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'is_flagged' => 'boolean',
        'locked_at' => 'datetime',
        'flagged_at' => 'datetime',
        'date_of_proofread' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function studentInfo()
    {
        return $this->belongsTo(StudentInfo::class, 'student_info_id');
    }

    public function proofreader()
    {
        return $this->belongsTo(User::class, 'proofreader_id');
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function flaggedBy()
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }

    public function isPending(): bool
    {
        return $this->review_status === 'pending';
    }

    public function isInReview(): bool
    {
        return $this->review_status === 'in_review';
    }

    public function isReviewed(): bool
    {
        return $this->review_status === 'reviewed';
    }

    public function isFlagged(): bool
    {
        return $this->is_flagged || $this->review_status === 'flagged';
    }

    public function isLocked(): bool
    {
        return $this->locked_by !== null && $this->locked_at !== null;
    }

    public function lockExpired(int $minutes = 15): bool
    {
        if (! $this->locked_at) {
            return false;
        }

        return $this->locked_at->lt(now()->subMinutes($minutes));
    }

    public function lockedByUser(User $user): bool
    {
        return (int) $this->locked_by === (int) $user->id;
    }
}
