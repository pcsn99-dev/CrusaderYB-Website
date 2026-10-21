<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penalty extends Model
{
    protected $table = 'penalties';

    public $timestamps = false;

    protected $fillable = [
        'student_info_id',
        'penalty_type',
        'reservation_id',
        'amount',
        'year',

        'is_paid',
        'status',
        'receipt_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function studentInfo(): BelongsTo
    {
        return $this->belongsTo(
            StudentInfo::class,
            'student_info_id'
        );
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(
            Reservation::class,
            'reservation_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy Relationships
    |--------------------------------------------------------------------------
    |
    | Keep these while older parts of the system may still reference them.
    |
    */

    public function student_info(): BelongsTo
    {
        return $this->studentInfo();
    }

    public function receipts()
    {
        return $this->belongsTo(
            '\App\Models\Receipts',
            'receipt_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getFullname(): ?string
    {
        return $this->studentInfo
            ? $this->studentInfo->formatted_full_name
            : null;
    }

    public function getReservationLink(): ?string
    {
        if (!$this->reservation) {
            return null;
        }

        $url = url(
            'reservation/'.$this->reservation->id
        );

        return '<a class="btn btn-link text-info btn-sm px-3 mb-0" '
            .'href="'.$url.'" '
            .'target="_blank" '
            .'data-bs-toggle="tooltip" '
            .'data-bs-original-title="Click to view reservation details">'
            .'<i class="bi bi-eye me-2" aria-hidden="true"></i>'
            .'View Details'
            .'</a>';
    }
}