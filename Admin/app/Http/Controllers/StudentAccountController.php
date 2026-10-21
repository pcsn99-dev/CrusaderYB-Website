<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\StudentInfo;
use App\Support\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\Penalty;
use App\Models\Pictorial;
use App\Services\PictorialAttendanceService;
use App\Models\Reservation;
use Illuminate\Validation\Rule;

use Throwable;

class StudentAccountController extends Controller
{
    public function index(): View
    {
        $colleges = College::query()
            ->orderBy('college_name')
            ->get([
                'id',
                'college_name',
            ]);

        $graduationYears = StudentInfo::query()
            ->whereNotNull('year')
            ->where('year', '!=', '')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->values();

        return view('student-accounts.index', [
            'colleges' => $colleges,
            'graduationYears' => $graduationYears,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'college_id' => ['nullable', 'integer', 'exists:colleges,id'],
            'year' => ['nullable', 'string', 'max:191'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim($validated['search'] ?? '');
        $collegeId = $validated['college_id'] ?? null;
        $year = $validated['year'] ?? null;

        if ($search === '' && empty($collegeId) && empty($year)) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 20,
                    'total' => 0,
                    'from' => null,
                    'to' => null,
                ],
            ]);
        }

        $students = StudentInfo::query()
            ->with([
                'college:id,college_name',
                'program:id,program_name',
                'major:id,major_name',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $terms = preg_split('/\s+/', $search);

                foreach ($terms as $term) {
                    $query->where(function ($studentQuery) use ($term) {
                        $studentQuery
                            ->where('university_id', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('middle_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('suffix', 'like', "%{$term}%");
                    });
                }
            })
            ->when($collegeId, function ($query) use ($collegeId) {
                $query->where('college_id', $collegeId);
            })
            ->when($year, function ($query) use ($year) {
                $query->where('year', $year);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20);

        $data = collect($students->items())
            ->map(function (StudentInfo $student) {
                return [
                    'id' => $student->id,
                    'university_id' => $student->university_id,
                    'full_name' => $student->formatted_full_name,
                    'college' => $student->college?->college_name,
                    'program' => $student->program?->program_name,
                    'major' => $student->major?->major_name,
                    'year' => $student->year,
                    'is_subscribe' => $student->is_subscribe,
                    'is_third_party' => $student->is_third_party,
                ];
            })
            ->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ],
        ]);
    }

    public function show(StudentInfo $student): View
    {
        $student->load([
            'user:id,email',
            'college:id,college_name',
            'program:id,program_name',
            'major:id,major_name',

            'reservations.pictorial',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reservations
        |--------------------------------------------------------------------------
        */

        $reservations = $student->reservations
            ->sortByDesc(function ($reservation) {
                return $reservation
                    ->pictorial
                    ?->date
                    ?->format('Y-m-d') ?? '';
            })
            ->values()
            ->map(function ($reservation) {
                return [
                    'id' =>
                        $reservation->id,

                    'is_rescheduled' =>
                        (bool)
                        $reservation->is_reschedule,

                    'reschedule_date' =>
                        $reservation->reschedule_date
                            ?->format('Y-m-d'),

                    'is_present_date' =>
                        $reservation->is_present_date
                            ?->format('Y-m-d'),

                    'attendance_status' =>
                        $reservation
                            ->getAttendanceStatus(),

                    'checked_in_at' =>
                        $reservation->checked_in_at
                            ?->format('Y-m-d H:i:s'),

                    'minutes_late' =>
                        $reservation
                            ->getMinutesLate(),

                    'created_at' =>
                        $reservation->created_at
                            ?->format('Y-m-d H:i:s'),

                    'pictorial' =>
                        $reservation->pictorial
                            ? [
                                'id' =>
                                    $reservation
                                        ->pictorial
                                        ->id,

                                'date' =>
                                    $reservation
                                        ->pictorial
                                        ->date
                                        ?->format('Y-m-d'),

                                'start_time' =>
                                    $reservation
                                        ->pictorial
                                        ->start_time,

                                'end_time' =>
                                    $reservation
                                        ->pictorial
                                        ->end_time,
                            ]
                            : null,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Penalties
        |--------------------------------------------------------------------------
        */

        $attendancePenaltyTypes = [
            PictorialAttendanceService::PENALTY_TYPE_LATE,
            PictorialAttendanceService::PENALTY_TYPE_ABSENT,
        ];

        $penalties = Penalty::query()
            ->with([
                'reservation.pictorial',
            ])
            ->where(
                'student_info_id',
                $student->id
            )
            ->orderByDesc('id')
            ->get()
            ->map(function ($penalty) use (
                $attendancePenaltyTypes
            ) {
                $reservation =
                    $penalty->reservation;

                $pictorial =
                    $reservation
                        ?->pictorial;

                $isAttendancePenalty =
                    $penalty->reservation_id !== null &&
                    in_array(
                        $penalty->penalty_type,
                        $attendancePenaltyTypes,
                        true
                    );

                return [
                    'id' =>
                        $penalty->id,

                    'penalty_type' =>
                        $penalty->penalty_type,

                    'penalty_type_label' =>
                        str(
                            $penalty->penalty_type
                        )
                            ->replace(
                                '_',
                                ' '
                            )
                            ->title()
                            ->toString(),

                    'amount' =>
                        (float)
                        $penalty->amount,

                    'source' =>
                        $isAttendancePenalty
                            ? 'attendance'
                            : 'manual',

                    'source_label' =>
                        $isAttendancePenalty
                            ? 'Attendance'
                            : 'Manual',

                    'reservation' =>
                        $reservation
                            ? [
                                'id' =>
                                    $reservation->id,

                                'date' =>
                                    $pictorial
                                        ?->date
                                        ?->format(
                                            'Y-m-d'
                                        ),

                                'start_time' =>
                                    $pictorial
                                        ?->start_time,

                                'end_time' =>
                                    $pictorial
                                        ?->end_time,
                            ]
                            : null,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Student Data
        |--------------------------------------------------------------------------
        */

        $studentData = [
            'id' =>
                $student->id,

            'university_id' =>
                $student->university_id,

            'slmis_id' =>
                $student->slmis_id,

            'first_name' =>
                $student->first_name,

            'middle_name' =>
                $student->middle_name,

            'last_name' =>
                $student->last_name,

            'suffix' =>
                $student->suffix,

            'full_name' =>
                $student
                    ->formatted_full_name,

            'email' =>
                $student
                    ->user
                    ?->email,

            'contact_number' =>
                $student
                    ->contact_number,

            'current_address' =>
                $student
                    ->current_address,

            'permanent_address' =>
                $student
                    ->permanent_address,

            'graduation_year' =>
                $student->year,

            'expected_graduation_date' =>
                $student
                    ->expected_graduation_date
                    ?->format('Y-m-d'),

            'college' =>
                $student
                    ->college
                    ?->college_name,

            'program' =>
                $student
                    ->program
                    ?->program_name,

            'major' =>
                $student
                    ->major
                    ?->major_name,

            'is_agree_contract' =>
                (bool)
                $student
                    ->is_agree_contract,

            'is_subscribe' =>
                (bool)
                $student
                    ->is_subscribe,

            'is_third_party' =>
                (bool)
                $student
                    ->is_third_party,

            'subscribe_date' =>
                $student
                    ->subscribe_date
                    ?->format('Y-m-d'),

            'unsubscribe_date' =>
                $student
                    ->unsubscribe_date
                    ?->format('Y-m-d'),

            'claim_pic' =>
                (bool)
                $student
                    ->claim_pic,

            'claim_pic_date' =>
                $student
                    ->claim_pic_date
                    ?->format('Y-m-d'),

            'reservations' =>
                $reservations,

            'penalties' =>
                $penalties,

            'permissions' => [
                'manage_subscription' =>
                    Auth::user()
                        ?->hasPermission(
                            'manage-student-subscription'
                        ) ?? false,

                'manage_third_party' =>
                    Auth::user()
                        ?->hasPermission(
                            'manage-third-party-status'
                        ) ?? false,
                'manage_reservations' =>
                    Auth::user()
                        ?->hasPermission(
                            'manage-student-reservations'
                        ) ?? false,
            ],
        ];

        return view(
            'student-accounts.show',
            [
                'student' =>
                    $studentData,
            ]
        );
    }


    public function availablePictorials(
        StudentInfo $student
    ): JsonResponse {
        if (!$student->year) {
            return response()->json([
                'message' =>
                    'This student does not have a CYB year assigned.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Current Reservation
        |--------------------------------------------------------------------------
        */

        $currentReservation =
            Reservation::query()
                ->with('pictorial')
                ->where(
                    'student_info_id',
                    $student->id
                )
                ->whereNull(
                    'cancelled_at'
                )
                ->where(
                    'is_reschedule',
                    false
                )
                ->latest('id')
                ->first();


        $pictorials = Pictorial::query()
            ->where(
                'year',
                $student->year
            )
            ->whereDate(
                'date',
                '>=',
                now()->toDateString()
            )
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(function (
                Pictorial $pictorial
            ) use ($currentReservation) {
                $reservedSlots =
                    Reservation::query()
                        ->where(
                            'pictorial_id',
                            $pictorial->id
                        )
                        ->whereNull(
                            'cancelled_at'
                        )
                        ->where(
                            'is_reschedule',
                            false
                        )
                        ->count();

                $availableSlots =
                    max(
                        0,
                        $pictorial->no_of_slots -
                        $reservedSlots
                    );

                return [
                    'id' =>
                        $pictorial->id,

                    'date' =>
                        $pictorial
                            ->date
                            ?->format('Y-m-d'),

                    'date_label' =>
                        $pictorial
                            ->date
                            ?->format('M d, Y'),

                    'start_time' =>
                        $pictorial
                            ->start_time,

                    'end_time' =>
                        $pictorial
                            ->end_time,

                    'time_label' =>
                        $pictorial
                            ->getPictorialTime(),

                    'no_of_slots' =>
                        (int)
                        $pictorial
                            ->no_of_slots,

                    'reserved_slots' =>
                        $reservedSlots,

                    'available_slots' =>
                        $availableSlots,

                    'is_available' =>
                        $availableSlots > 0,

                    'is_delayed' =>
                        (bool)
                        $pictorial
                            ->is_delayed,

                    'delayed_penalty' =>
                        $pictorial->is_delayed
                            ? $this->delayedPictorialPenalty()
                            : null,                            

                    'is_current_reservation' =>
                        $currentReservation
                            ?->pictorial_id ===
                        $pictorial->id,
                ];
            })
            ->filter(
                fn (array $schedule) =>
                    $schedule['is_available'] ||
                    $schedule[
                        'is_current_reservation'
                    ]
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Rescheduling Penalty Preview
        |--------------------------------------------------------------------------
        */

        $reschedulePenalty = null;

        if (
            $currentReservation &&
            $currentReservation->pictorial
        ) {
            $originalDate = Carbon::parse(
                $currentReservation
                    ->pictorial
                    ->date
                    ->format('Y-m-d')
            )->startOfDay();

            $today =
                now()->startOfDay();

            $daysBefore =
                $today->lt($originalDate)
                    ? (int) $today->diffInDays(
                        $originalDate
                    )
                    : 0;

            $reschedulePenalty =
                $this->resolveReschedulePenalty(
                    $daysBefore
                );
        }

        return response()->json([
            'data' => [
                'student' => [
                    'id' =>
                        $student->id,

                    'name' =>
                        $student
                            ->formatted_full_name,

                    'year' =>
                        $student->year,
                ],

                'current_reservation' =>
                    $currentReservation
                        ? [
                            'id' =>
                                $currentReservation
                                    ->id,

                            'pictorial_id' =>
                                $currentReservation
                                    ->pictorial_id,

                            'date' =>
                                $currentReservation
                                    ->pictorial
                                    ?->date
                                    ?->format(
                                        'Y-m-d'
                                    ),

                            'date_label' =>
                                $currentReservation
                                    ->pictorial
                                    ?->date
                                    ?->format(
                                        'M d, Y'
                                    ),

                            'time_label' =>
                                $currentReservation
                                    ->pictorial
                                    ?->getPictorialTime(),
                        ]
                        : null,

                'reschedule_penalty' =>
                    $reschedulePenalty,

                'schedules' =>
                    $pictorials,
            ],
        ]);
    }


    public function storeReservation(
        Request $request,
        StudentInfo $student
    ): JsonResponse {
        $validated = $request->validate([
            'pictorial_id' => [
                'required',
                'integer',

                Rule::exists(
                    'pictorials',
                    'id'
                ),
            ],

            'waive_penalty' => [
                'nullable',
                'boolean',
            ],
        ]);

        $pictorial =
            Pictorial::query()
                ->where(
                    'id',
                    $validated[
                        'pictorial_id'
                    ]
                )
                ->where(
                    'year',
                    $student->year
                )
                ->first();

        if (!$pictorial) {
            return response()->json([
                'message' =>
                    'The selected pictorial schedule is not valid for this student\'s CYB year.',
            ], 422);
        }

        if (
            $pictorial->date &&
            $pictorial->date->lt(
                now()->startOfDay()
            )
        ) {
            return response()->json([
                'message' =>
                    'Past pictorial schedules cannot be booked.',
            ], 422);
        }

        $currentReservation =
            Reservation::query()
                ->with('pictorial')
                ->where(
                    'student_info_id',
                    $student->id
                )
                ->whereNull(
                    'cancelled_at'
                )
                ->where(
                    'is_reschedule',
                    false
                )
                ->latest('id')
                ->first();

        if (
            $currentReservation &&
            $currentReservation
                ->pictorial_id ===
            $pictorial->id
        ) {
            return response()->json([
                'message' =>
                    'The student is already booked for this pictorial schedule.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Check Capacity
        |--------------------------------------------------------------------------
        */

        $reservedSlots =
            Reservation::query()
                ->where(
                    'pictorial_id',
                    $pictorial->id
                )
                ->whereNull(
                    'cancelled_at'
                )
                ->where(
                    'is_reschedule',
                    false
                )
                ->count();

        if (
            $reservedSlots >=
            $pictorial->no_of_slots
        ) {
            return response()->json([
                'message' =>
                    'The selected pictorial schedule is already full.',
            ], 422);
        }

        $waivePenalty =
            (bool)
            ($validated[
                'waive_penalty'
            ] ?? false);

        $result = DB::transaction(
            function () use (
                $student,
                $pictorial,
                $currentReservation,
                $waivePenalty
            ) {
                $reschedulePenalty = null;

                /*
                |--------------------------------------------------------------------------
                | Existing Reservation -> Reschedule
                |--------------------------------------------------------------------------
                */

                if (
                    $currentReservation &&
                    $currentReservation
                        ->pictorial
                ) {
                    $originalDate =
                        Carbon::parse(
                            $currentReservation
                                ->pictorial
                                ->date
                                ->format(
                                    'Y-m-d'
                                )
                        )
                            ->startOfDay();

                    $today =
                        now()
                            ->startOfDay();

                    $daysBefore =
                        $today->lt(
                            $originalDate
                        )
                            ? (int) $today
                                ->diffInDays(
                                    $originalDate
                                )
                            : 0;

                    $reschedulePenalty =
                        $this
                            ->resolveReschedulePenalty(
                                $daysBefore
                            );

                    $currentReservation->update([
                        'is_reschedule' =>
                            true,

                        'reschedule_date' =>
                            now()
                                ->toDateString(),
                    ]);

                    if (
                        !$waivePenalty &&
                        $reschedulePenalty
                    ) {
                        Penalty::create([
                            'student_info_id' =>
                                $student->id,

                            'penalty_type' =>
                                $reschedulePenalty[
                                    'penalty_type'
                                ],

                            'reservation_id' =>
                                $currentReservation
                                    ->id,

                            'amount' =>
                                $reschedulePenalty[
                                    'amount'
                                ],
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Create New Current Reservation
                |--------------------------------------------------------------------------
                */

                $reservation =
                    Reservation::create([
                        'student_info_id' =>
                            $student->id,

                        'pictorial_id' =>
                            $pictorial->id,

                        'is_present' =>
                            null,

                        'checked_in_at' =>
                            null,

                        'is_reschedule' =>
                            false,

                        'is_present_date' =>
                            null,

                        'reschedule_date' =>
                            null,

                        'cancelled_at' =>
                            null,

                        'cancellation_reason' =>
                            null,
                    ]);
                    
                $delayedPenalty = null;

                if ($pictorial->is_delayed) {
                    $delayedPenaltyRule =
                        $this->delayedPictorialPenalty();

                    $delayedPenalty =
                        Penalty::create([
                            'student_info_id' =>
                                $student->id,

                            'penalty_type' =>
                                $delayedPenaltyRule[
                                    'penalty_type'
                                ],

                            'reservation_id' =>
                                $reservation->id,

                            'amount' =>
                                $delayedPenaltyRule[
                                    'amount'
                                ],
                        ]);
                }

                AuditLogger::record(
                    'student_reservations',
                    $currentReservation
                        ? 'reservation_rescheduled'
                        : 'reservation_created',

                    $currentReservation
                        ? sprintf(
                            'Rescheduled pictorial reservation for %s.',
                            $student
                                ->formatted_full_name
                        )
                        : sprintf(
                            'Created pictorial reservation for %s.',
                            $student
                                ->formatted_full_name
                        ),

                    $reservation,

                    $currentReservation
                        ? [
                            'reservation_id' =>
                                $currentReservation
                                    ->id,

                            'pictorial_id' =>
                                $currentReservation
                                    ->pictorial_id,
                        ]
                        : null,

                    [
                        'reservation_id' =>
                            $reservation->id,

                        'pictorial_id' =>
                            $pictorial->id,

                        'waived_penalty' =>
                            $currentReservation
                                ? $waivePenalty
                                : false,

                        'reschedule_penalty' =>
                            $currentReservation
                                ? $reschedulePenalty
                                : null,

                        'delayed_schedule_penalty' =>
                            $delayedPenalty
                                ? [
                                    'penalty_id' =>
                                        $delayedPenalty->id,

                                    'penalty_type' =>
                                        $delayedPenalty
                                            ->penalty_type,

                                    'amount' =>
                                        (float)
                                        $delayedPenalty
                                            ->amount,
                                ]
                                : null,

                    ]
                );

                return [
                    'reservation' =>
                        $reservation,

                    'was_rescheduled' =>
                        $currentReservation !== null,

                    'penalty' =>
                        $reschedulePenalty,

                    'penalty_waived' =>
                        $currentReservation
                            ? $waivePenalty
                            : false,

                    'delayed_penalty' =>
                        $delayedPenalty
                            ? [
                                'id' =>
                                    $delayedPenalty->id,

                                'penalty_type' =>
                                    $delayedPenalty
                                        ->penalty_type,

                                'label' =>
                                    $this
                                        ->delayedPictorialPenalty()[
                                            'label'
                                        ],

                                'amount' =>
                                    (float)
                                    $delayedPenalty
                                        ->amount,
                            ]
                            : null,
                ];
            }
        );

        return response()->json([
            'message' =>
                $result['was_rescheduled']
                    ? (
                        $result[
                            'penalty_waived'
                        ]
                            ? 'Reservation rescheduled successfully. The rescheduling penalty was waived.'
                            : 'Reservation rescheduled successfully.'
                    )
                    : 'Reservation created successfully.',

            'data' => [
                'reservation_id' =>
                    $result[
                        'reservation'
                    ]->id,

                'was_rescheduled' =>
                    $result[
                        'was_rescheduled'
                    ],

                'penalty' =>
                    $result[
                        'was_rescheduled'
                    ] &&
                    !$result[
                        'penalty_waived'
                    ]
                        ? $result[
                            'penalty'
                        ]
                        : null,

                'penalty_waived' =>
                    $result[
                        'penalty_waived'
                    ],

                'delayed_penalty' =>
                    $result[
                        'delayed_penalty'
                    ],               
                    
            ],
        ], 201);
    }

    public function updateSubscription(
        Request $request,
        StudentInfo $student
    ): JsonResponse {
        $validated = $request->validate([
            'is_subscribe' => ['required', 'boolean'],
        ]);

        $newStatus = (bool) $validated['is_subscribe'];
        $oldStatus = (bool) $student->is_subscribe;

        if ($oldStatus === $newStatus) {
            return response()->json([
                'message' => 'Subscription status is already up to date.',
                'student' => [
                    'is_subscribe' => (bool) $student->is_subscribe,
                    'subscribe_date' => $student->subscribe_date?->format('Y-m-d'),
                    'unsubscribe_date' => $student->unsubscribe_date?->format('Y-m-d'),
                ],
            ]);
        }

        try {
            DB::transaction(function () use (
                $student,
                $newStatus,
                $oldStatus
            ) {
                $oldValues = [
                    'is_subscribe' => $oldStatus,
                    'subscribe_date' => $student->subscribe_date?->format('Y-m-d'),
                    'unsubscribe_date' => $student->unsubscribe_date?->format('Y-m-d'),
                ];

                if ($newStatus) {
                    $student->is_subscribe = true;
                    $student->subscribe_date = now()->toDateString();
                    $student->unsubscribe_date = null;
                } else {
                    $student->is_subscribe = false;
                    $student->unsubscribe_date = now()->toDateString();
                }

                $student->save();

                $newValues = [
                    'is_subscribe' => (bool) $student->is_subscribe,
                    'subscribe_date' => $student->subscribe_date?->format('Y-m-d'),
                    'unsubscribe_date' => $student->unsubscribe_date?->format('Y-m-d'),
                ];

                $adminName = Auth::user()?->name ?? 'Unknown admin';

                AuditLogger::record(
                    module: 'student_accounts',
                    action: 'subscription_status_updated',
                    description:
                        "{$adminName} changed subscription status for ".
                        "{$student->formatted_full_name} ".
                        "({$student->university_id}) from ".
                        ($oldStatus ? 'subscribed' : 'not subscribed').
                        ' to '.
                        ($newStatus ? 'subscribed' : 'not subscribed').'.',
                    model: $student,
                    oldValues: $oldValues,
                    newValues: $newValues
                );
            });

            $student->refresh();

            return response()->json([
                'message' => $newStatus
                    ? 'Student subscribed successfully.'
                    : 'Student unsubscribed successfully.',

                'student' => [
                    'is_subscribe' => (bool) $student->is_subscribe,
                    'subscribe_date' => $student->subscribe_date?->format('Y-m-d'),
                    'unsubscribe_date' => $student->unsubscribe_date?->format('Y-m-d'),
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to update the subscription status.',
            ], 500);
        }
    }


    public function updateThirdPartyStatus(
        Request $request,
        StudentInfo $student
    ): JsonResponse {
        $validated = $request->validate([
            'is_third_party' => ['required', 'boolean'],
        ]);

        $newStatus = (bool) $validated['is_third_party'];
        $oldStatus = (bool) $student->is_third_party;

        if ($oldStatus === $newStatus) {
            return response()->json([
                'message' => 'Third-party photo status is already up to date.',
                'student' => [
                    'is_third_party' => (bool) $student->is_third_party,
                ],
            ]);
        }

        try {
            DB::transaction(function () use (
                $student,
                $newStatus,
                $oldStatus
            ) {
                $oldValues = [
                    'is_third_party' => $oldStatus,
                ];

                $student->is_third_party = $newStatus;
                $student->save();

                $newValues = [
                    'is_third_party' => (bool) $student->is_third_party,
                ];

                $adminName = Auth::user()?->name ?? 'Unknown admin';

                AuditLogger::record(
                    module: 'student_accounts',
                    action: 'third_party_status_updated',
                    description:
                        "{$adminName} changed third-party photo status for ".
                        "{$student->formatted_full_name} ".
                        "({$student->university_id}) from ".
                        ($oldStatus ? 'third-party' : 'CYB pictorial').
                        ' to '.
                        ($newStatus ? 'third-party' : 'CYB pictorial').'.',
                    model: $student,
                    oldValues: $oldValues,
                    newValues: $newValues
                );
            });

            $student->refresh();

            return response()->json([
                'message' => $newStatus
                    ? 'Student marked as third-party photo.'
                    : 'Third-party photo status removed.',

                'student' => [
                    'is_third_party' => (bool) $student->is_third_party,
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Unable to update third-party photo status.',
            ], 500);
        }
    }


    private function resolveReschedulePenalty(
        int $daysBefore
    ): array {
        if ($daysBefore >= 4) {
            return [
                'penalty_type' =>
                    'pictorial_reschedule_4_plus_days',

                'label' =>
                    'Pictorial Rescheduling — 4+ Days Before',

                'amount' =>
                    150.00,

                'days_before' =>
                    $daysBefore,
            ];
        }

        if ($daysBefore >= 1) {
            return [
                'penalty_type' =>
                    'pictorial_reschedule_1_to_3_days',

                'label' =>
                    'Pictorial Rescheduling — 1 to 3 Days Before',

                'amount' =>
                    350.00,

                'days_before' =>
                    $daysBefore,
            ];
        }

        return [
            'penalty_type' =>
                'pictorial_reschedule_same_day_or_missed',

            'label' =>
                'Pictorial Rescheduling — Same Day / Missed Schedule',

            'amount' =>
                500.00,

            'days_before' =>
                0,
        ];
    }

    private function delayedPictorialPenalty(): array
    {
        return [
            'penalty_type' =>
                'pictorial_delayed_schedule',

            'label' =>
                'Delayed Pictorial Schedule',

            'amount' =>
                300.00,
        ];
}    

}