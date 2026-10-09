<?php

namespace App\Services;

use App\Models\Pictorial;
use App\Models\Year;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PictorialScheduleService
{
    public function createSingle(array $data): Pictorial
    {
        $activeYear = Year::active()->first();

        if (!$activeYear) {
            throw ValidationException::withMessages([
                'year' => 'No active CYB year is configured.',
            ]);
        }

        $isDelayed = (bool) $data['is_delayed'];

        $collegeIds = $isDelayed
            ? array_map('intval', $data['allowed_college_ids'] ?? [])
            : [(int) $data['college_id']];

        if (
            $this->hasConflict(
                year: $activeYear->year,
                date: $data['date'],
                startTime: $data['start_time'],
                endTime: $data['end_time'],
                collegeIds: $collegeIds,
            )
        ) {
            throw ValidationException::withMessages([
                'schedule' =>
                    'This schedule overlaps an existing pictorial schedule for one or more selected colleges.',
            ]);
        }

        return DB::transaction(function () use (
            $data,
            $activeYear,
            $isDelayed,
            $collegeIds
        ) {
            $pictorial = Pictorial::create([
                'year' => $activeYear->year,

                'college_id' => $isDelayed
                    ? null
                    : $collegeIds[0],

                'date' => $data['date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'no_of_slots' => $data['no_of_slots'],
                'is_delayed' => $isDelayed,

                // Single-created schedules do not belong
                // to a bulk creation batch.
                'batch_uuid' => null,
            ]);

            if ($isDelayed) {
                $pictorial
                    ->allowedColleges()
                    ->sync($collegeIds);
            }

            return $pictorial;
        });
    }

    public function hasConflict(
        string $year,
        string $date,
        string $startTime,
        string $endTime,
        array $collegeIds,
    ): bool {
        return Pictorial::query()
            ->forYear($year)
            ->whereDate('date', $date)

            // Existing schedule overlaps new schedule when:
            //
            // existing.start < new.end
            // AND
            // existing.end > new.start
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)

            ->where(function (Builder $query) use ($collegeIds) {
                // Regular schedules belonging directly
                // to one of the target colleges.
                $query->where(function (Builder $query) use ($collegeIds) {
                    $query
                        ->where('is_delayed', false)
                        ->whereIn('college_id', $collegeIds);
                })

                // Delayed schedules where at least one of the
                // target colleges is allowed.
                ->orWhere(function (Builder $query) use ($collegeIds) {
                    $query
                        ->where('is_delayed', true)
                        ->whereHas(
                            'allowedColleges',
                            fn (Builder $collegeQuery) =>
                                $collegeQuery->whereIn(
                                    'colleges.id',
                                    $collegeIds
                                )
                        );
                });
            })
            ->exists();
    }
}