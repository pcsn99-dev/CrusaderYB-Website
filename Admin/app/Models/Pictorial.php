<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pictorial extends Model
{
    use SoftDeletes;

    protected $table = 'pictorials';

    protected $fillable = [
        'year',
        'college_id',
        'date',
        'start_time',
        'end_time',
        'no_of_slots',
        'is_delayed',
    ];

    protected $casts = [
        'date' => 'date',
        'no_of_slots' => 'integer',
        'is_delayed' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class, 'college_id');
    }

    public function allowedColleges(): BelongsToMany
    {
        return $this->belongsToMany(
            College::class,
            'pictorial_colleges',
            'pictorial_id',
            'college_id'
        )->withTimestamps();
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'pictorial_id');
    }

    public function activeReservations(): HasMany
    {
        return $this->reservations()
            ->whereNull('cancelled_at')
            ->where('is_reschedule', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForYear(
        Builder $query,
        string $year
    ): Builder {
        return $query->where('year', $year);
    }

    public function scopeNormal(Builder $query): Builder
    {
        return $query->where('is_delayed', false);
    }

    public function scopeDelayed(Builder $query): Builder
    {
        return $query->where('is_delayed', true);
    }

    public function scopeForCollege(
        Builder $query,
        int $collegeId
    ): Builder {
        return $query->where('college_id', $collegeId);
    }

    public function scopeAvailableToCollege(
        Builder $query,
        int $collegeId
    ): Builder {
        return $query->where(function (Builder $query) use ($collegeId) {
            $query
                ->where(function (Builder $query) use ($collegeId) {
                    $query
                        ->where('is_delayed', false)
                        ->where('college_id', $collegeId);
                })
                ->orWhere(function (Builder $query) use ($collegeId) {
                    $query
                        ->where('is_delayed', true)
                        ->whereHas(
                            'allowedColleges',
                            fn (Builder $collegeQuery) =>
                                $collegeQuery->where(
                                    'colleges.id',
                                    $collegeId
                                )
                        );
                });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getRemainingSlots(): int
    {
        return max(
            0,
            $this->no_of_slots - $this->activeReservations()->count()
        );
    }

    public function getReservedSlots(): int
    {
        return $this->activeReservations()->count();
    }

    public function isFull(): bool
    {
        return $this->getRemainingSlots() <= 0;
    }

    public function getPictorialDate(): string
    {
        return Carbon::parse($this->date)->format('M d, Y');
    }

    public function getPictorialTime(): string
    {
        return Carbon::parse($this->start_time)->format('h:i A')
            .' - '
            .Carbon::parse($this->end_time)->format('h:i A');
    }
}