<?php

namespace App\Services;

use App\Models\Pictorial;
use App\Models\Year;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

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


public function previewBulk(array $data): array
{
    $activeYear = Year::active()->first();

    if (!$activeYear) {
        throw ValidationException::withMessages([
            'year' => 'No active CYB year is configured.',
        ]);
    }

    $generatedSchedules = $this->generateBulkSchedules($data);

    if (empty($generatedSchedules)) {
        throw ValidationException::withMessages([
            'schedule' =>
                'The selected date range and weekend options do not produce any schedules.',
        ]);
    }

    $isDelayed = (bool) $data['is_delayed'];

    $collegeIds = $isDelayed
        ? array_map(
            'intval',
            $data['allowed_college_ids'] ?? []
        )
        : [(int) $data['college_id']];

    $conflicts = [];

    foreach ($generatedSchedules as $schedule) {
        if (
            $this->hasConflict(
                year: $activeYear->year,
                date: $schedule['date'],
                startTime: $schedule['start_time'],
                endTime: $schedule['end_time'],
                collegeIds: $collegeIds,
            )
        ) {
            $conflicts[] = $schedule;
        }
    }

    return [
        'year' => $activeYear->year,
        'total' => count($generatedSchedules),
        'conflict_count' => count($conflicts),
        'can_create' => count($conflicts) === 0,

        'schedules' => $generatedSchedules,

        'conflicts' => $conflicts,
    ];
}

public function createBulk(array $data): array
{
    $preview = $this->previewBulk($data);

    if (!$preview['can_create']) {
        throw ValidationException::withMessages([
            'schedule' =>
                'One or more generated schedules conflict with existing pictorial schedules. Review the preview and adjust the bulk schedule.',
        ]);
    }

    $activeYear = Year::active()->firstOrFail();

    $isDelayed = (bool) $data['is_delayed'];

    $collegeIds = $isDelayed
        ? array_map(
            'intval',
            $data['allowed_college_ids'] ?? []
        )
        : [(int) $data['college_id']];

    $batchUuid = (string) Str::uuid();

    $created = DB::transaction(function () use (
        $preview,
        $activeYear,
        $data,
        $isDelayed,
        $collegeIds,
        $batchUuid
    ) {
        $createdPictorials = [];

        foreach ($preview['schedules'] as $schedule) {
            $pictorial = Pictorial::create([
                'year' => $activeYear->year,

                'college_id' => $isDelayed
                    ? null
                    : $collegeIds[0],

                'date' => $schedule['date'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],

                'no_of_slots' => $data['no_of_slots'],

                'is_delayed' => $isDelayed,

                'batch_uuid' => $batchUuid,
            ]);

            if ($isDelayed) {
                $pictorial
                    ->allowedColleges()
                    ->sync($collegeIds);
            }

            $createdPictorials[] = $pictorial;
        }

        return $createdPictorials;
    });

    return [
        'batch_uuid' => $batchUuid,
        'created_count' => count($created),
    ];
}

private function generateBulkSchedules(array $data): array
{
    $dateFrom = CarbonImmutable::parse(
        $data['date_from']
    )->startOfDay();

    $dateTo = CarbonImmutable::parse(
        $data['date_to']
    )->startOfDay();

    $slotDuration = (int) $data['slot_duration_minutes'];

    $includeSaturday =
        (bool) $data['include_saturday'];

    $includeSunday =
        (bool) $data['include_sunday'];

    $generated = [];

    for (
        $date = $dateFrom;
        $date->lte($dateTo);
        $date = $date->addDay()
    ) {
        if (
            $date->isSaturday() &&
            !$includeSaturday
        ) {
            continue;
        }

        if (
            $date->isSunday() &&
            !$includeSunday
        ) {
            continue;
        }

        $dayStart = CarbonImmutable::parse(
            $date->format('Y-m-d')
            .' '
            .$data['daily_start_time']
        );

        $dayEnd = CarbonImmutable::parse(
            $date->format('Y-m-d')
            .' '
            .$data['daily_end_time']
        );

        $slotStart = $dayStart;

        while ($slotStart->lt($dayEnd)) {
            $slotEnd = $slotStart->addMinutes(
                $slotDuration
            );

            /*
             * Do not create a shortened final slot.
             *
             * Example:
             * 8:00–9:00 with 40-minute duration
             *
             * Creates:
             * 8:00–8:40
             *
             * Does NOT create:
             * 8:40–9:00
             */
            if ($slotEnd->gt($dayEnd)) {
                break;
            }

            $generated[] = [
                'date' => $date->format('Y-m-d'),

                'date_label' =>
                    $date->format('M d, Y'),

                'start_time' =>
                    $slotStart->format('H:i'),

                'end_time' =>
                    $slotEnd->format('H:i'),

                'time_label' =>
                    $slotStart->format('h:i A')
                    .' - '
                    .$slotEnd->format('h:i A'),
            ];

            $slotStart = $slotEnd;
        }
    }

    return $generated;
}

}