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
    public function scopeForReviewQueue($query)
    {
        return $query->with([
            'studentInfo.college',
            'studentInfo.program',
            'studentInfo.major',
            'proofreader',
            'lockedBy',
            'flaggedBy',
        ])
        ->whereHas('studentInfo');
    }





    //SCOPES 
    
    public function scopeFilterByYear($query, ?string $year)
    {
        return $query->when($year, function ($query) use ($year) {
            $query->whereHas('studentInfo', function ($query) use ($year) {
                $query->where('year', $year);
            });
        });
    }

    public function scopeFilterByCollege($query, $collegeId)
    {
        return $query->when($collegeId, function ($query) use ($collegeId) {
            $query->whereHas('studentInfo', function ($query) use ($collegeId) {
                $query->where('college_id', $collegeId);
            });
        });
    }

    public function scopeFilterByStatus($query, ?string $status)
    {
        return $query->when($status, function ($query) use ($status) {
            $query->where('review_status', $status);
        });
    }

    public function scopeFlaggedOnly($query, bool $flaggedOnly = false)
    {
        return $query->when($flaggedOnly, function ($query) {
            $query->where('is_flagged', true);
        });
    }

    public function scopeSearchStudent($query, ?string $search)
    {
        return $query->when($search, function ($query) use ($search) {
            $query->whereHas('studentInfo', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('university_id', 'like', "%{$search}%")
                        ->orWhere('slmis_id', 'like', "%{$search}%");
                });
            });
        });
    }

    public function scopeOrderedForReviewQueue($query)
    {
        return $query
            ->join('student_info', 'student_info.id', '=', 'writeups.student_info_id')
            ->leftJoin('colleges', 'colleges.id', '=', 'student_info.college_id')
            ->leftJoin('programs', 'programs.id', '=', 'student_info.program_id')
            ->orderBy('colleges.college_name')
            ->orderBy('programs.program_name')
            ->orderBy('student_info.last_name')
            ->orderBy('student_info.first_name')
            ->select('writeups.*');
    }



}
