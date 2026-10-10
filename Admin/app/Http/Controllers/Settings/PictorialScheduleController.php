<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Pictorial;
use App\Models\Year;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\Settings\StorePictorialScheduleRequest;
use App\Services\PictorialScheduleService;
use App\Http\Requests\Settings\BulkStorePictorialScheduleRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Support\AuditLogger;


class PictorialScheduleController extends Controller
{
    public function index(): View
    {
        $this->authorizeAccess();

        $activeYear = Year::active()->first();

        $colleges = College::query()
            ->orderBy('college_name')
            ->get([
                'id',
                'college_name',
            ]);

        $canManage = auth()
            ->user()
            ?->hasPermission('manage-pictorial-schedules') ?? false;

        return view(
            'settings.pictorial-schedules.index',
            compact(
                'activeYear',
                'colleges',
                'canManage'
            )
        );
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'college_id' => [
                'nullable',
                'integer',
                'exists:colleges,id',
            ],
            'type' => [
                'nullable',
                'in:regular,delayed',
            ],
            'date_from' => [
                'nullable',
                'date',
            ],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
            'availability' => [
                'nullable',
                'in:available,full',
            ],
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $activeYear = Year::active()->first();

        if (!$activeYear) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 25,
                    'total' => 0,
                    'from' => null,
                    'to' => null,
                ],
            ]);
        }

        $query = Pictorial::query()
            ->forYear($activeYear->year)
            ->with([
                'college:id,college_name',
                'allowedColleges:id,college_name',
            ])
            ->withCount([
                'activeReservations as reserved_slots',
            ]);

        if (!empty($validated['college_id'])) {
            $query->availableToCollege(
                (int) $validated['college_id']
            );
        }

        if (($validated['type'] ?? null) === 'regular') {
            $query->normal();
        }

        if (($validated['type'] ?? null) === 'delayed') {
            $query->delayed();
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate(
                'date',
                '>=',
                $validated['date_from']
            );
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate(
                'date',
                '<=',
                $validated['date_to']
            );
        }

        if (($validated['availability'] ?? null) === 'available') {
            $query->whereRaw(
                '(
                    SELECT COUNT(*)
                    FROM reservations
                    WHERE reservations.pictorial_id = pictorials.id
                    AND reservations.cancelled_at IS NULL
                    AND reservations.is_reschedule = 0
                ) < pictorials.no_of_slots'
            );
        }

        if (($validated['availability'] ?? null) === 'full') {
            $query->whereRaw(
                '(
                    SELECT COUNT(*)
                    FROM reservations
                    WHERE reservations.pictorial_id = pictorials.id
                    AND reservations.cancelled_at IS NULL
                    AND reservations.is_reschedule = 0
                ) >= pictorials.no_of_slots'
            );
        }

        $pictorials = $query
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(25);

        return response()->json([
            'data' => collect($pictorials->items())
                ->map(function (Pictorial $pictorial) {
                    return [
                        'id' => $pictorial->id,
                        'batch_uuid' => $pictorial->batch_uuid,
                        'year' => $pictorial->year,
                        'date' => $pictorial->date->format('Y-m-d'),
                        'date_label' => $pictorial->date->format('M d, Y'),
                        'start_time' => $pictorial->start_time,
                        'end_time' => $pictorial->end_time,
                        'time_label' => $pictorial->getPictorialTime(),

                        'college' => $pictorial->college
                            ? [
                                'id' => $pictorial->college->id,
                                'college_name' => $pictorial->college->college_name,
                            ]
                            : null,

                        'allowed_colleges' => $pictorial
                            ->allowedColleges
                            ->map(fn ($college) => [
                                'id' => $college->id,
                                'college_name' => $college->college_name,
                            ])
                            ->values(),

                        'is_delayed' => $pictorial->is_delayed,

                        'no_of_slots' => $pictorial->no_of_slots,

                        'reserved_slots' => (int) $pictorial->reserved_slots,

                        'remaining_slots' => max(
                            0,
                            $pictorial->no_of_slots -
                            $pictorial->reserved_slots
                        ),

                        'is_full' =>
                            $pictorial->reserved_slots >=
                            $pictorial->no_of_slots,
                    ];
                })
                ->values(),

            'meta' => [
                'current_page' => $pictorials->currentPage(),
                'last_page' => $pictorials->lastPage(),
                'per_page' => $pictorials->perPage(),
                'total' => $pictorials->total(),
                'from' => $pictorials->firstItem(),
                'to' => $pictorials->lastItem(),
            ],
        ]);
    }

    public function deleteBatch(string $batchUuid): array
    {
        $activeYear = Year::active()->first();

        if (!$activeYear) {
            throw ValidationException::withMessages([
                'batch' => 'No active CYB year is configured.',
            ]);
        }

        $pictorials = Pictorial::query()
            ->forYear($activeYear->year)
            ->where('batch_uuid', $batchUuid)
            ->with([
                'college:id,college_name',
                'allowedColleges:id,college_name',
            ])
            ->withCount([
                'activeReservations as reserved_slots',
            ])
            ->get();

        if ($pictorials->isEmpty()) {
            throw ValidationException::withMessages([
                'batch' => 'This pictorial batch could not be found.',
            ]);
        }

        $reservationCount = $pictorials->sum('reserved_slots');

        if ($reservationCount > 0) {
            throw ValidationException::withMessages([
                'batch' =>
                    "This batch has {$reservationCount} active reservation(s). Review the affected students before deleting it.",
            ]);
        }

        /*
        * Capture audit information before the schedules
        * are soft-deleted.
        */
        $auditModel = $pictorials->first();

        $deletedScheduleIds = $pictorials
            ->pluck('id')
            ->values()
            ->all();

        $dateFrom = $pictorials
            ->min(fn (Pictorial $pictorial) =>
                $pictorial->date->format('Y-m-d')
            );

        $dateTo = $pictorials
            ->max(fn (Pictorial $pictorial) =>
                $pictorial->date->format('Y-m-d')
            );

        $deletedCount = DB::transaction(function () use ($pictorials) {
            foreach ($pictorials as $pictorial) {
                $pictorial->delete();
            }

            return $pictorials->count();
        });

        $adminName = auth()->user()->name ?? 'Unknown admin';

        AuditLogger::record(
            module: 'pictorial_schedules',
            action: 'batch_deleted',
            description:
                "{$adminName} deleted a bulk pictorial schedule batch ".
                "containing {$deletedCount} schedules for CYB ".
                "{$activeYear->year}.",
            model: $auditModel,
            oldValues: [
                'batch_uuid' => $batchUuid,
                'schedule_ids' => $deletedScheduleIds,
                'schedule_count' => $deletedCount,
                'year' => $activeYear->year,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            newValues: []
        );

        return [
            'deleted_count' => $deletedCount,
            'batch_uuid' => $batchUuid,
        ];
    }



    private function authorizeAccess(): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('view-pictorial-schedules') ||
            $user?->hasPermission('manage-pictorial-schedules'),
            403
        );
    }

    public function store(
        StorePictorialScheduleRequest $request,
        PictorialScheduleService $scheduleService
    ): JsonResponse {
        $pictorial = $scheduleService->createSingle(
            $request->validated()
        );

        $pictorial->load([
            'college:id,college_name',
            'allowedColleges:id,college_name',
        ]);

        $pictorial->loadCount([
            'activeReservations as reserved_slots',
        ]);

        $adminName = auth()->user()->name ?? 'Unknown admin';

        $collegeDescription = $pictorial->is_delayed
            ? $pictorial
                ->allowedColleges
                ->pluck('college_name')
                ->join(', ')
            : ($pictorial->college?->college_name ?? 'Unknown college');

        AuditLogger::record(
            module: 'pictorial_schedules',
            action: 'schedule_created',
            description:
                "{$adminName} created a ".
                ($pictorial->is_delayed ? 'delayed' : 'regular').
                " pictorial schedule for {$collegeDescription} on ".
                $pictorial->date->format('M d, Y').
                " from {$pictorial->start_time} to {$pictorial->end_time}.",
            model: $pictorial,
            oldValues: [],
            newValues: [
                'id' => $pictorial->id,
                'year' => $pictorial->year,
                'college_id' => $pictorial->college_id,
                'allowed_college_ids' => $pictorial
                    ->allowedColleges
                    ->pluck('id')
                    ->values()
                    ->all(),
                'date' => $pictorial->date->format('Y-m-d'),
                'start_time' => $pictorial->start_time,
                'end_time' => $pictorial->end_time,
                'no_of_slots' => $pictorial->no_of_slots,
                'is_delayed' => $pictorial->is_delayed,
            ]
        );

        return response()->json([
            'message' => 'Pictorial schedule created successfully.',
            'data' => [
                'id' => $pictorial->id,
            ],
        ], 201);
    }

    public function previewBulk(
        BulkStorePictorialScheduleRequest $request,
        PictorialScheduleService $scheduleService
    ): JsonResponse {
        $preview = $scheduleService->previewBulk(
            $request->validated()
        );

        return response()->json([
            'data' => $preview,
        ]);
    }

    public function storeBulk(
        BulkStorePictorialScheduleRequest $request,
        PictorialScheduleService $scheduleService
    ): JsonResponse {
        $validated = $request->validated();

        $result = $scheduleService->createBulk(
            $validated
        );

        $adminName = auth()->user()->name ?? 'Unknown admin';

        $firstPictorial = Pictorial::query()
            ->where('batch_uuid', $result['batch_uuid'])
            ->first();

        AuditLogger::record(
            module: 'pictorial_schedules',
            action: 'batch_created',
            description:
                "{$adminName} bulk-created ".
                "{$result['created_count']} pictorial schedules ".
                "for CYB {$firstPictorial?->year}.",
            model: $firstPictorial,
            oldValues: [],
            newValues: [
                'batch_uuid' => $result['batch_uuid'],
                'created_count' => $result['created_count'],
                'year' => $firstPictorial?->year,
                'is_delayed' => (bool) $validated['is_delayed'],
                'college_id' => $validated['college_id'] ?? null,
                'allowed_college_ids' =>
                    $validated['allowed_college_ids'] ?? [],
                'date_from' => $validated['date_from'],
                'date_to' => $validated['date_to'],
                'daily_start_time' => $validated['daily_start_time'],
                'daily_end_time' => $validated['daily_end_time'],
                'slot_duration_minutes' =>
                    $validated['slot_duration_minutes'],
                'no_of_slots' => $validated['no_of_slots'],
                'include_saturday' =>
                    (bool) $validated['include_saturday'],
                'include_sunday' =>
                    (bool) $validated['include_sunday'],
            ]
        );

        return response()->json([
            'message' =>
                "{$result['created_count']} pictorial schedules created successfully.",

            'data' => $result,
        ], 201);
    }


    public function batches(): JsonResponse
    {
        $this->authorizeAccess();

        $activeYear = Year::active()->first();

        if (!$activeYear) {
            return response()->json([
                'data' => [],
            ]);
        }

        $batches = Pictorial::query()
            ->forYear($activeYear->year)
            ->whereNotNull('batch_uuid')
            ->select('batch_uuid')
            ->selectRaw('COUNT(*) as schedule_count')
            ->selectRaw('MIN(date) as date_from')
            ->selectRaw('MAX(date) as date_to')
            ->groupBy('batch_uuid')
            ->orderByDesc('date_from')
            ->get()
            ->map(function ($batch) use ($activeYear) {
                $reservationCount = Pictorial::query()
                    ->forYear($activeYear->year)
                    ->where('batch_uuid', $batch->batch_uuid)
                    ->withCount([
                        'activeReservations as reserved_slots',
                    ])
                    ->get()
                    ->sum('reserved_slots');

                return [
                    'batch_uuid' => $batch->batch_uuid,

                    'schedule_count' =>
                        (int) $batch->schedule_count,

                    'reservation_count' =>
                        (int) $reservationCount,

                    'date_from' =>
                        $batch->date_from,

                    'date_to' =>
                        $batch->date_to,
                ];
            })
            ->values();

        return response()->json([
            'data' => $batches,
        ]);
    }   
    
    public function deleteSelected(
        Request $request
    ): JsonResponse {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('manage-pictorial-schedules'),
            403
        );

        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'integer',
                'distinct',
                'exists:pictorials,id',
            ],
        ]);

        $activeYear = Year::active()->first();

        if (!$activeYear) {
            return response()->json([
                'message' => 'No active CYB year is configured.',
            ], 422);
        }

        $pictorials = Pictorial::query()
            ->forYear($activeYear->year)
            ->whereIn('id', $validated['ids'])
            ->withCount([
                'activeReservations as reserved_slots',
            ])
            ->get();

        if ($pictorials->count() !== count($validated['ids'])) {
            return response()->json([
                'message' =>
                    'One or more selected schedules do not belong to the active CYB year.',
            ], 422);
        }

        $reservationCount =
            $pictorials->sum('reserved_slots');

        if ($reservationCount > 0) {
            return response()->json([
                'message' =>
                    'Some selected schedules have active reservations.',

                'errors' => [
                    'schedules' => [
                        "{$reservationCount} active reservation(s) are affected. Review them before deleting these schedules.",
                    ],
                ],
            ], 422);
        }

        /*
        * Capture values before soft deletion.
        */
        $auditModel = $pictorials->first();

        $deletedScheduleIds = $pictorials
            ->pluck('id')
            ->values()
            ->all();

        $deletedSchedules = $pictorials
            ->map(fn (Pictorial $pictorial) => [
                'id' => $pictorial->id,
                'date' => $pictorial->date->format('Y-m-d'),
                'start_time' => $pictorial->start_time,
                'end_time' => $pictorial->end_time,
                'college_id' => $pictorial->college_id,
                'is_delayed' => $pictorial->is_delayed,
                'batch_uuid' => $pictorial->batch_uuid,
            ])
            ->values()
            ->all();

        DB::transaction(function () use ($pictorials) {
            foreach ($pictorials as $pictorial) {
                $pictorial->delete();
            }
        });

        $deletedCount = $pictorials->count();

        $adminName = auth()->user()->name ?? 'Unknown admin';

        AuditLogger::record(
            module: 'pictorial_schedules',
            action: 'selected_schedules_deleted',
            description:
                "{$adminName} deleted {$deletedCount} selected ".
                "pictorial schedule".
                ($deletedCount === 1 ? '' : 's').
                " for CYB {$activeYear->year}.",
            model: $auditModel,
            oldValues: [
                'schedule_ids' => $deletedScheduleIds,
                'schedule_count' => $deletedCount,
                'year' => $activeYear->year,
                'schedules' => $deletedSchedules,
            ],
            newValues: []
        );

        return response()->json([
            'message' =>
                "{$deletedCount} pictorial schedule(s) deleted successfully.",
        ]);
    }

    public function reservations(Pictorial $pictorial): JsonResponse
    {
        $this->authorizeAccess();

        $activeYear = Year::active()->first();

        if (!$activeYear) {
            return response()->json([
                'message' => 'No active CYB year is configured.',
            ], 422);
        }

        if ($pictorial->year !== $activeYear->year) {
            abort(404);
        }

        $reservations = $pictorial
            ->activeReservations()
            ->with([
                'studentInfo:id,user_id,university_id,slmis_id,first_name,middle_name,last_name,suffix,college_id,program_id,major_id,contact_number',
                'studentInfo.college:id,college_name',
                'studentInfo.program:id,program_name',
                'studentInfo.major:id,major_name',
                'studentInfo.user:id,email',
            ])
            ->orderBy('created_at')
            ->get()
            ->map(function ($reservation) {
                $student = $reservation->studentInfo;

                return [
                    'id' => $reservation->id,

                    'student' => [
                        'id' => $student?->id,
                        'name' => $student?->formatted_full_name,
                        'university_id' => $student?->university_id,
                        'slmis_id' => $student?->slmis_id,
                        'email' => $student?->user?->email,
                        'contact_number' => $student?->contact_number,

                        'college' => $student?->college?->college_name,
                        'program' => $student?->program?->program_name,
                        'major' => $student?->major?->major_name,
                    ],

                    'created_at' => $reservation->created_at?->format(
                        'M d, Y h:i A'
                    ),
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'pictorial' => [
                    'id' => $pictorial->id,
                    'date' => $pictorial->date->format('Y-m-d'),
                    'date_label' => $pictorial->date->format('M d, Y'),
                    'time_label' => $pictorial->getPictorialTime(),
                ],

                'reservations' => $reservations,
                'reservation_count' => $reservations->count(),
            ],
        ]);
    }



}