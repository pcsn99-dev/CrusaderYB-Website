<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $table = 'reservations';

    protected $fillable = [
        'pictorial_id',
        'student_info_id',
        'is_present',
        'is_reschedule',
        'is_present_date',
        'reschedule_date',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'is_present' => 'integer',
        'is_reschedule' => 'boolean',
        'is_present_date' => 'date',
        'reschedule_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function pictorial(): BelongsTo
    {
        return $this->belongsTo(Pictorial::class, 'pictorial_id');
    }

    public function studentInfo(): BelongsTo
    {
        return $this->belongsTo(StudentInfo::class, 'student_info_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('cancelled_at')
            ->where('is_reschedule', false);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->whereNotNull('cancelled_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function cancel(
        string $reason = 'Pictorial schedule cancelled by admin'
    ): void {
        $this->update([
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function getPictorialSchedule(): ?string
    {
        if (!$this->pictorial) {
            return null;
        }

        return Carbon::parse($this->pictorial->date)->format('M j, Y')
            .' '
            .Carbon::parse($this->pictorial->start_time)->format('h:i A');
    }

    public function getFullname(): ?string
    {
        if (!$this->studentInfo) {
            return null;
        }

        return $this->studentInfo->formatted_full_name;
    }

    public function getReservationStatus(): string
    {
        if ($this->isCancelled()) {
            return '<span class="badge text-bg-danger ms-auto">Cancelled</span>';
        }

        if ($this->is_present === 1) {
            return '<span class="badge text-bg-success ms-auto">Present</span>';
        }

        if ($this->is_present === 0) {
            return '<span class="badge text-bg-danger ms-auto">Absent</span>';
        }

        if (!is_null($this->is_present)) {
            return '<span class="badge text-bg-warning ms-auto">Late</span>';
        }

        return '<span class="badge text-bg-secondary ms-auto">NA</span>';
    }

    public function getTimestamp(): string
    {
        if (is_null($this->timestamp)) {
            return '<span class="badge text-bg-secondary ms-auto">NA</span>';
        }

        return '<span class="ms-auto">'
            .e($this->timestamp)
            .'</span>';
    }

    public function getReservationLink(): ?string
    {
        if (!isset($this->id)) {
            return null;
        }

        $url = url('reservation/'.$this->id);

        return '<a class="btn btn-link text-info btn-sm px-3 mb-0" '
            .'href="'.$url.'" target="_blank" '
            .'data-bs-toggle="tooltip" '
            .'data-bs-original-title="Click to view reservation details">'
            .'<i class="bi bi-eye me-2" aria-hidden="true"></i>'
            .'View Details</a>';
    }

    public function ifReschedule(): ?string
    {
        if (!isset($this->id)) {
            return null;
        }

        if (
            $this->reschedule_date !== null &&
            $this->is_reschedule
        ) {
            return '<span class="badge text-bg-secondary ms-auto">'
                .'(RESCHEDULED)'
                .'</span>';
        }

        return '<span class="badge text-bg-success ms-auto">'
            .'CURRENT RESERVATION SCHED.'
            .'</span>';
    }
}