<?php

namespace App\Http\Controllers;

use App\Models\Penalty;
use App\Models\Reservation;
use App\Models\Year;
use App\Services\PictorialAttendanceService;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PictorialAttendanceController extends Controller
{

    public function index(): View
    {
        $activeYear = Year::active()->first();

        $user = auth()->user();

        return view('pictorial-attendance.index', [
            'activeYear' => $activeYear,

            'canManage' =>
                $user?->hasPermission(
                    'manage-pictorial-attendance'
                ) ?? false,

            'canCancelReservations' =>
                $user?->hasPermission(
                    'manage-student-reservations'
                ) ?? false,
        ]);
    }

    public function reservations(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'date' => [
                'required',
                'date_format:Y-m-d',
            ],
        ]);

        $activeYear = Year::active()->first();

        if (!$activeYear) {
            return response()->json([
                'message' =>
                    'No active CYB year is configured.',
                'data' => [],
            ], 422);
        }

        $date = $validated['date'];

        /*
        |--------------------------------------------------------------------------
        | Load applicable reservations for the selected date
        |--------------------------------------------------------------------------
        |
        | active() means:
        |
        | cancelled_at IS NULL
        | is_reschedule = false
        |
        | This prevents old/cancelled/rescheduled reservations from appearing.
        |
        */

        $reservations = Reservation::query()
            ->active()
            ->whereHas(
                'pictorial',
                function ($query) use (
                    $date,
                    $activeYear
                ) {
                    $query
                        ->whereDate('date', $date)
                        ->where(
                            'year',
                            $activeYear->year
                        );
                }
            )
            ->with([
                'pictorial:id,year,college_id,date,start_time,end_time,is_delayed',
                'studentInfo:id,user_id,university_id,slmis_id,first_name,middle_name,last_name,suffix,college_id,program_id,major_id,contact_number',
                'studentInfo.user:id,email',
                'studentInfo.college:id,college_name',
                'studentInfo.program:id,program_name',
                'studentInfo.major:id,major_name',
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Load attendance penalties in one query
        |--------------------------------------------------------------------------
        */

        $reservationIds = $reservations
            ->pluck('id')
            ->values()
            ->all();

        $penalties = Penalty::query()
            ->whereIn(
                'reservation_id',
                $reservationIds
            )
            ->whereIn(
                'penalty_type',
                [
                    PictorialAttendanceService::PENALTY_TYPE_LATE,
                    PictorialAttendanceService::PENALTY_TYPE_ABSENT,
                ]
            )
            ->get()
            ->keyBy('reservation_id');

        /*
        |--------------------------------------------------------------------------
        | Sort by schedule time, then student name
        |--------------------------------------------------------------------------
        */

        $reservations = $reservations
            ->sortBy(function (
                Reservation $reservation
            ) {
                $scheduleTime =
                    $reservation->pictorial
                        ?->start_time ?? '99:99:99';

                $studentName =
                    $reservation->studentInfo
                        ?->formatted_full_name ?? '';

                return $scheduleTime
                    .'|'
                    .$studentName;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Format response
        |--------------------------------------------------------------------------
        */

        $data = $reservations
            ->map(function (
                Reservation $reservation
            ) use ($penalties) {
                $student =
                    $reservation->studentInfo;

                $pictorial =
                    $reservation->pictorial;

                $penalty =
                    $penalties->get(
                        $reservation->id
                    );

                $minutesLate =
                    $reservation->isPresent()
                        ? $reservation->getMinutesLate()
                        : null;

                return [
                    'id' => $reservation->id,

                    'student' => [
                        'id' =>
                            $student?->id,

                        'name' =>
                            $student
                                ?->formatted_full_name,

                        'university_id' =>
                            $student?->university_id,

                        'slmis_id' =>
                            $student?->slmis_id,

                        'email' =>
                            $student
                                ?->user
                                ?->email,

                        'contact_number' =>
                            $student?->contact_number,

                        'college' =>
                            $student
                                ?->college
                                ?->college_name,

                        'program' =>
                            $student
                                ?->program
                                ?->program_name,

                        'major' =>
                            $student
                                ?->major
                                ?->major_name,
                    ],

                    'schedule' => [
                        'id' =>
                            $pictorial?->id,

                        'date' =>
                            $pictorial
                                ?->date
                                ?->format('Y-m-d'),

                        'date_label' =>
                            $pictorial
                                ?->date
                                ?->format('M d, Y'),

                        'start_time' =>
                            $pictorial?->start_time,

                        'end_time' =>
                            $pictorial?->end_time,

                        'time_label' =>
                            $pictorial
                                ? $pictorial
                                    ->getPictorialTime()
                                : null,
                    ],

                    'attendance' => [
                        'status' =>
                            $reservation
                                ->getAttendanceStatus(),

                        'checked_in_at' =>
                            $reservation
                                ->checked_in_at
                                ?->format(
                                    'Y-m-d H:i:s'
                                ),

                        'checked_in_time' =>
                            $reservation
                                ->checked_in_at
                                ?->format('h:i A'),

                        'checked_in_time_value' =>
                            $reservation
                                ->checked_in_at
                                ?->format('H:i'),

                        'minutes_late' =>
                            $minutesLate,
                    ],

                    'penalty' => $penalty
                        ? [
                            'id' =>
                                $penalty->id,

                            'type' =>
                                $penalty->penalty_type,

                            'amount' =>
                                (float)
                                $penalty->amount,
                        ]
                        : null,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Summary counts
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total' => $data->count(),

            'pending' => $data
                ->where(
                    'attendance.status',
                    'pending'
                )
                ->count(),

            'present' => $data
                ->where(
                    'attendance.status',
                    'present'
                )
                ->count(),

            'late' => $data
                ->where(
                    'attendance.status',
                    'late'
                )
                ->count(),

            'absent' => $data
                ->where(
                    'attendance.status',
                    'absent'
                )
                ->count(),
        ];

        return response()->json([
            'data' => $data,

            'meta' => [
                'date' => $date,
                'active_year' =>
                    $activeYear->year,
                'summary' => $summary,
            ],
        ]);
    }


    public function updateAttendance(
        Request $request,
        Reservation $reservation,
        PictorialAttendanceService $attendanceService
    ): JsonResponse {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('manage-pictorial-attendance'),
            403
        );



        if (
            $reservation->cancelled_at !== null ||
            $reservation->is_reschedule
        ) {
            return response()->json([
                'message' =>
                    'This reservation is no longer active and cannot be updated.',
            ], 422);
        }

        $reservation->loadMissing([
            'pictorial',
            'studentInfo',
        ]);

        if (!$reservation->pictorial) {
            return response()->json([
                'message' =>
                    'This reservation does not have a pictorial schedule.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate requested action
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'action' => [
                'required',
                Rule::in([
                    'present',
                    'absent',
                    'revert',
                ]),
            ],

            'arrival_time' => [
                'nullable',
                'date_format:H:i',
                'required_if:action,present',
            ],
        ]);

        $action = $validated['action'];

        /*
        |--------------------------------------------------------------------------
        | Capture previous state for auditing
        |--------------------------------------------------------------------------
        */

        $oldPenalty = Penalty::query()
            ->where(
                'reservation_id',
                $reservation->id
            )
            ->whereIn(
                'penalty_type',
                [
                    PictorialAttendanceService::PENALTY_TYPE_LATE,
                    PictorialAttendanceService::PENALTY_TYPE_ABSENT,
                ]
            )
            ->first();

        $oldValues = [
            'is_present' =>
                $reservation->is_present,

            'checked_in_at' =>
                $reservation->checked_in_at?->toDateTimeString(),

            'attendance_status' =>
                $reservation->getAttendanceStatus(),

            'penalty' => $oldPenalty
                ? [
                    'id' => $oldPenalty->id,
                    'penalty_type' =>
                        $oldPenalty->penalty_type,
                    'amount' =>
                        (float) $oldPenalty->amount,
                ]
                : null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Apply attendance change
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $action,
            $validated,
            $reservation,
            $attendanceService
        ) {
            if ($action === 'present') {
                $checkedInAt =
                    $attendanceService
                        ->buildCheckedInAt(
                            $reservation,
                            $validated['arrival_time']
                        );

                $reservation->update([
                    'is_present' => 1,
                    'checked_in_at' =>
                        $checkedInAt,

                    /*
                     * Legacy field is intentionally not used
                     * by the new attendance system.
                     */
                    'is_present_date' => null,
                ]);

                $reservation->refresh();

                $attendanceService
                    ->syncAttendancePenalty(
                        $reservation
                    );

                return;
            }

            if ($action === 'absent') {
                $reservation->update([
                    'is_present' => 0,
                    'checked_in_at' => null,
                    'is_present_date' => null,
                ]);

                $reservation->refresh();

                $attendanceService
                    ->syncAttendancePenalty(
                        $reservation
                    );

                return;
            }

            /*
             * Revert
             */
            $reservation->update([
                'is_present' => null,
                'checked_in_at' => null,
                'is_present_date' => null,
            ]);

            $attendanceService
                ->removeAttendancePenalty(
                    $reservation
                );

            $reservation->refresh();
        });

        /*
        |--------------------------------------------------------------------------
        | Reload resulting state
        |--------------------------------------------------------------------------
        */

        $reservation->refresh();

        $newPenalty = Penalty::query()
            ->where(
                'reservation_id',
                $reservation->id
            )
            ->whereIn(
                'penalty_type',
                [
                    PictorialAttendanceService::PENALTY_TYPE_LATE,
                    PictorialAttendanceService::PENALTY_TYPE_ABSENT,
                ]
            )
            ->first();

        $minutesLate =
            $reservation->isPresent()
                ? $reservation->getMinutesLate()
                : null;

        $newValues = [
            'is_present' =>
                $reservation->is_present,

            'checked_in_at' =>
                $reservation->checked_in_at?->toDateTimeString(),

            'attendance_status' =>
                $reservation->getAttendanceStatus(),

            'minutes_late' =>
                $minutesLate,

            'penalty' => $newPenalty
                ? [
                    'id' => $newPenalty->id,
                    'penalty_type' =>
                        $newPenalty->penalty_type,
                    'amount' =>
                        (float) $newPenalty->amount,
                ]
                : null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        $adminName =
            auth()->user()->name ??
            'Unknown admin';

        $studentName =
            $reservation
                ->studentInfo
                ?->formatted_full_name ??
            'Unknown student';

        $auditAction = match ($action) {
            'present' => 'attendance_marked_present',
            'absent' => 'attendance_marked_absent',
            'revert' => 'attendance_reverted',
        };

        $description = match ($action) {
            'present' =>
                "{$adminName} marked {$studentName} as ".
                $reservation->getAttendanceStatus().
                '.',

            'absent' =>
                "{$adminName} marked {$studentName} as absent.",

            'revert' =>
                "{$adminName} reverted the attendance status of {$studentName}.",
        };

        AuditLogger::record(
            module: 'pictorial_attendance',
            action: $auditAction,
            description: $description,
            model: $reservation,
            oldValues: $oldValues,
            newValues: $newValues
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => match ($action) {
                'present' =>
                    $reservation->isLate()
                        ? 'Student marked as late successfully.'
                        : 'Student marked as present successfully.',

                'absent' =>
                    'Student marked as absent successfully.',

                'revert' =>
                    'Attendance reverted successfully.',
            },

            'data' => [
                'reservation_id' =>
                    $reservation->id,

                'status' =>
                    $reservation->getAttendanceStatus(),

                'checked_in_at' =>
                    $reservation
                        ->checked_in_at
                        ?->format('Y-m-d H:i:s'),

                'checked_in_time' =>
                    $reservation
                        ->checked_in_at
                        ?->format('h:i A'),

                'minutes_late' =>
                    $minutesLate,

                'penalty' => $newPenalty
                    ? [
                        'id' =>
                            $newPenalty->id,

                        'type' =>
                            $newPenalty->penalty_type,

                        'amount' =>
                            (float) $newPenalty->amount,
                    ]
                    : null,
            ],
        ]);
    }

    
}