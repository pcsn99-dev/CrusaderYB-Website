<?php

namespace App\Services;

use App\Models\Penalty;
use App\Models\Reservation;
use Carbon\Carbon;

class PictorialAttendanceService
{
    public const PENALTY_TYPE_LATE = 'pictorial_late';
    public const PENALTY_TYPE_ABSENT = 'pictorial_absent';

    public const LATE_PENALTY_AMOUNT = 150;
    public const SEVERE_LATE_PENALTY_AMOUNT = 500;
    public const ABSENT_PENALTY_AMOUNT = 500;

    /*
    |--------------------------------------------------------------------------
    | Attendance Time
    |--------------------------------------------------------------------------
    */

    public function buildCheckedInAt(
        Reservation $reservation,
        string $arrivalTime
    ): Carbon {
        $reservation->loadMissing('pictorial');

        if (!$reservation->pictorial) {
            throw new \RuntimeException(
                'Reservation does not have a pictorial schedule.'
            );
        }

        return Carbon::parse(
            $reservation->pictorial->date->format('Y-m-d')
            .' '
            .$arrivalTime
        );
    }

    public function calculateMinutesLate(
        Reservation $reservation,
        Carbon $checkedInAt
    ): int {
        $reservation->loadMissing('pictorial');

        if (!$reservation->pictorial) {
            throw new \RuntimeException(
                'Reservation does not have a pictorial schedule.'
            );
        }

        $scheduledAt = Carbon::parse(
            $reservation->pictorial->date->format('Y-m-d')
            .' '
            .$reservation->pictorial->start_time
        );

        if (
            $checkedInAt->lessThanOrEqualTo(
                $scheduledAt
            )
        ) {
            return 0;
        }

        return $scheduledAt->diffInMinutes(
            $checkedInAt
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Penalty Calculation
    |--------------------------------------------------------------------------
    */

    public function calculateLatePenalty(
        int $minutesLate
    ): float {
        if ($minutesLate <= 5) {
            return 0;
        }

        if ($minutesLate < 15) {
            return self::LATE_PENALTY_AMOUNT;
        }

        return self::SEVERE_LATE_PENALTY_AMOUNT;
    }

    public function calculateAbsentPenalty(): float
    {
        return self::ABSENT_PENALTY_AMOUNT;
    }

    /*
    |--------------------------------------------------------------------------
    | Penalty Synchronization
    |--------------------------------------------------------------------------
    */

    public function syncAttendancePenalty(
        Reservation $reservation
    ): ?Penalty {

        $reservation->loadMissing(
            'pictorial'
        );

        if (!$reservation->pictorial) {
            throw new \RuntimeException(
                'Reservation does not have a pictorial schedule.'
            );
        }


        $this->removeAttendancePenalty(
            $reservation
        );

 
        if ($reservation->isPendingAttendance()) {
            return null;
        }

        /*
         * Absent = ₱500.
         */
        if ($reservation->isAbsent()) {
            return Penalty::create([
                'student_info_id' =>
                    $reservation->student_info_id,

                'reservation_id' =>
                    $reservation->id,

                'penalty_type' =>
                    self::PENALTY_TYPE_ABSENT,

                'amount' =>
                    $this->calculateAbsentPenalty(),

                'year' =>
                    $reservation
                        ->pictorial
                        ->year,

            ]);
        }

        /*
         * Present students need their lateness calculated.
         */
        if (!$reservation->checked_in_at) {
            return null;
        }

        $minutesLate =
            $this->calculateMinutesLate(
                $reservation,
                $reservation->checked_in_at
            );

        $amount =
            $this->calculateLatePenalty(
                $minutesLate
            );

        /*
         * 0–5 minutes late = no penalty.
         */
        if ($amount <= 0) {
            return null;
        }

        return Penalty::create([
            'student_info_id' =>
                $reservation->student_info_id,

            'reservation_id' =>
                $reservation->id,

            'penalty_type' =>
                self::PENALTY_TYPE_LATE,

            'amount' => $amount,

            'year' =>
                $reservation
                    ->pictorial
                    ->year,
        ]);
    }

    public function removeAttendancePenalty(
        Reservation $reservation
    ): void {
        Penalty::query()
            ->where(
                'reservation_id',
                $reservation->id
            )
            ->whereIn(
                'penalty_type',
                [
                    self::PENALTY_TYPE_LATE,
                    self::PENALTY_TYPE_ABSENT,
                ]
            )
            ->delete();
    }
}