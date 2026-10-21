<?php

namespace App\Http\Controllers;

use App\Models\Penalty;
use App\Models\StudentInfo;
use App\Models\Year;
use App\Services\PictorialAttendanceService;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenaltyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Penalty Management Page
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $years = Year::query()
            ->orderByDesc('year')
            ->get([
                'id',
                'year',
                'status',
            ]);

        return view('penalties.index', [
            'years' => $years,

            'canManage' => auth()
                ->user()
                ?->hasPermission('manage-penalties') ?? false,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Search / List Penalties
    |--------------------------------------------------------------------------
    */

    public function search(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'year' => [
                'required',
                'string',
                'max:10',
            ],

            'q' => [
                'nullable',
                'string',
                'max:150',
            ],

            'penalty_type' => [
                'nullable',
                'string',
                'max:191',
            ],

            'source' => [
                'nullable',
                'in:attendance,other',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:10',
                'max:100',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query = Penalty::query()
            ->with([
                'studentInfo:id,university_id,slmis_id,first_name,middle_name,last_name,suffix,college_id,program_id,year',

                'studentInfo.college:id,college_name',

                'studentInfo.program:id,program_name',

                'reservation:id,pictorial_id,student_info_id,is_present,checked_in_at,cancelled_at',

                'reservation.pictorial:id,year,date,start_time,end_time',
            ])
            ->whereHas(
                'studentInfo',
                function ($studentQuery) use ($validated) {
                    $studentQuery->where(
                        'year',
                        $validated['year']
                    );
                }
            );

        /*
        |--------------------------------------------------------------------------
        | Student Search
        |--------------------------------------------------------------------------
        */

        $search = trim(
            $validated['q'] ?? ''
        );

        if ($search !== '') {
            $query->whereHas(
                'studentInfo',
                function ($studentQuery) use ($search) {
                    $studentQuery->where(
                        function ($searchQuery) use ($search) {
                            $searchQuery
                                ->where(
                                    'first_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'middle_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'last_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'suffix',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'university_id',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'slmis_id',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Penalty Type Filter
        |--------------------------------------------------------------------------
        */

        if (
            !empty(
                $validated['penalty_type']
            )
        ) {
            $query->where(
                'penalty_type',
                $validated['penalty_type']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Source Filter
        |--------------------------------------------------------------------------
        |
        | Attendance-generated penalties have:
        |
        | - pictorial_late / pictorial_absent
        | - AND a linked reservation
        |
        | A manually-created Late or Absent penalty has no reservation link,
        | so it is treated as Manual / Other.
        |
        */

        $attendanceTypes =
            $this->attendancePenaltyTypes();

        if (
            ($validated['source'] ?? null) ===
            'attendance'
        ) {
            $query
                ->whereNotNull(
                    'reservation_id'
                )
                ->whereIn(
                    'penalty_type',
                    $attendanceTypes
                );
        }

        if (
            ($validated['source'] ?? null) ===
            'other'
        ) {
            $query->where(
                function ($sourceQuery) use (
                    $attendanceTypes
                ) {
                    $sourceQuery
                        ->whereNull(
                            'reservation_id'
                        )
                        ->orWhereNotIn(
                            'penalty_type',
                            $attendanceTypes
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        $totalRecords = (
            clone $query
        )->count();

        $totalAmount = (
            clone $query
        )->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage =
            $validated['per_page'] ?? 25;

        $penalties = $query
            ->orderByDesc('id')
            ->paginate($perPage);

        /*
        |--------------------------------------------------------------------------
        | Format Response
        |--------------------------------------------------------------------------
        */

        $data = collect(
            $penalties->items()
        )
            ->map(
                function (
                    Penalty $penalty
                ) use ($attendanceTypes) {
                    $student =
                        $penalty->studentInfo;

                    $reservation =
                        $penalty->reservation;

                    $pictorial =
                        $reservation?->pictorial;

                    $isAttendancePenalty =
                        $penalty->reservation_id !== null &&
                        in_array(
                            $penalty->penalty_type,
                            $attendanceTypes,
                            true
                        );

                    return [
                        'id' =>
                            $penalty->id,

                        'penalty_type' =>
                            $penalty->penalty_type,

                        'penalty_type_label' =>
                            $this->formatPenaltyType(
                                $penalty->penalty_type
                            ),

                        'amount' =>
                            (float)
                            $penalty->amount,

                        'source' =>
                            $isAttendancePenalty
                                ? 'attendance'
                                : 'other',

                        'source_label' =>
                            $isAttendancePenalty
                                ? 'Attendance'
                                : 'Manual / Other',

                        'student' => [
                            'id' =>
                                $student?->id,

                            'name' =>
                                $student
                                    ?->formatted_full_name,

                            'university_id' =>
                                $student
                                    ?->university_id,

                            'slmis_id' =>
                                $student
                                    ?->slmis_id,

                            'year' =>
                                $student
                                    ?->year,

                            'college' =>
                                $student
                                    ?->college
                                    ?->college_name,

                            'program' =>
                                $student
                                    ?->program
                                    ?->program_name,
                        ],

                        'reservation' =>
                            $reservation
                                ? [
                                    'id' =>
                                        $reservation->id,

                                    'pictorial_id' =>
                                        $reservation
                                            ->pictorial_id,

                                    'date' =>
                                        $pictorial
                                            ?->date
                                            ?->format(
                                                'Y-m-d'
                                            ),

                                    'date_label' =>
                                        $pictorial
                                            ?->date
                                            ?->format(
                                                'M d, Y'
                                            ),

                                    'time_label' =>
                                        $pictorial
                                            ? $pictorial
                                                ->getPictorialTime()
                                            : null,

                                    'year' =>
                                        $pictorial
                                            ?->year,
                                ]
                                : null,
                    ];
                }
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Available Penalty Types
        |--------------------------------------------------------------------------
        */

        $penaltyTypes = Penalty::query()
            ->whereHas(
                'studentInfo',
                function ($studentQuery) use ($validated) {
                    $studentQuery->where(
                        'year',
                        $validated['year']
                    );
                }
            )
            ->whereNotNull(
                'penalty_type'
            )
            ->where(
                'penalty_type',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy(
                'penalty_type'
            )
            ->pluck(
                'penalty_type'
            )
            ->map(
                fn (
                    string $type
                ) => [
                    'value' =>
                        $type,

                    'label' =>
                        $this->formatPenaltyType(
                            $type
                        ),
                ]
            )
            ->values();

        return response()->json([
            'data' => $data,

            'meta' => [
                'current_page' =>
                    $penalties
                        ->currentPage(),

                'last_page' =>
                    $penalties
                        ->lastPage(),

                'per_page' =>
                    $penalties
                        ->perPage(),

                'total' =>
                    $penalties
                        ->total(),

                'from' =>
                    $penalties
                        ->firstItem(),

                'to' =>
                    $penalties
                        ->lastItem(),

                'total_records' =>
                    $totalRecords,

                'total_amount' =>
                    (float)
                    $totalAmount,

                'year' =>
                    $validated['year'],
            ],

            'filters' => [
                'penalty_types' =>
                    $penaltyTypes,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Student Search
    |--------------------------------------------------------------------------
    |
    | Used by the Add Penalty modal.
    |
    | Only students belonging to the selected CYB year are returned.
    |
    */

    public function students(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'year' => [
                'required',
                'string',
                'max:10',
            ],

            'q' => [
                'required',
                'string',
                'min:1',
                'max:150',
            ],
        ]);

        $search = trim(
            $validated['q']
        );

        $students = StudentInfo::query()
            ->with([
                'college:id,college_name',
                'program:id,program_name',
            ])
            ->where(
                'year',
                $validated['year']
            )
            ->where(
                function ($query) use ($search) {
                    $query
                        ->where(
                            'first_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'middle_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'last_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'suffix',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'university_id',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'slmis_id',
                            'like',
                            "%{$search}%"
                        );
                }
            )
            ->orderBy(
                'last_name'
            )
            ->orderBy(
                'first_name'
            )
            ->limit(20)
            ->get();

        $data = $students
            ->map(
                function (
                    StudentInfo $student
                ) {
                    return [
                        'id' =>
                            $student->id,

                        'name' =>
                            $student
                                ->formatted_full_name,

                        'university_id' =>
                            $student
                                ->university_id,

                        'slmis_id' =>
                            $student
                                ->slmis_id,

                        'year' =>
                            $student->year,

                        'college' =>
                            $student
                                ->college
                                ?->college_name,

                        'program' =>
                            $student
                                ->program
                                ?->program_name,
                    ];
                }
            )
            ->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create Penalty
    |--------------------------------------------------------------------------
    |
    | Manual penalty creation currently supports:
    |
    | pictorial_late_minor
    |     -> pictorial_late
    |     -> PHP 150
    |
    | pictorial_late_severe
    |     -> pictorial_late
    |     -> PHP 500
    |
    | pictorial_absent
    |     -> pictorial_absent
    |     -> PHP 500
    |
    | pictorial_unauthorized_photo
    |     -> pictorial_unauthorized_photo
    |     -> PHP 500
    |
    | The frontend does NOT determine the final amount. The backend maps
    | the selected option to its official amount to prevent tampering.
    |
    */

    public function store(
        Request $request
    ): JsonResponse {
        $penaltyOptions =
            $this->manualPenaltyOptions();

        $validated = $request->validate([
            'year' => [
                'required',
                'string',
                'max:10',
            ],

            'student_info_id' => [
                'required',
                'integer',

                Rule::exists(
                    'student_info',
                    'id'
                ),
            ],

            'penalty_code' => [
                'required',
                'string',

                Rule::in(
                    array_keys(
                        $penaltyOptions
                    )
                ),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Verify Student Belongs To Selected Year
        |--------------------------------------------------------------------------
        */

        $student = StudentInfo::query()
            ->with([
                'college:id,college_name',
                'program:id,program_name',
            ])
            ->where(
                'id',
                $validated['student_info_id']
            )
            ->where(
                'year',
                $validated['year']
            )
            ->first();

        if (!$student) {
            return response()->json([
                'message' =>
                    'The selected student does not belong to the selected CYB year.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Official Penalty Rule
        |--------------------------------------------------------------------------
        */

        $option =
            $penaltyOptions[
                $validated['penalty_code']
            ];

        /*
        |--------------------------------------------------------------------------
        | Create Penalty
        |--------------------------------------------------------------------------
        |
        | Manual penalties intentionally have no reservation_id.
        |
        | We also intentionally do not touch:
        |
        | - is_paid
        | - status
        | - receipt_id
        |
        */

        $penalty = Penalty::create([
            'student_info_id' =>
                $student->id,

            'penalty_type' =>
                $option['penalty_type'],

            'reservation_id' =>
                null,

            'amount' =>
                $option['amount'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        AuditLogger::record(
            'penalties',
            'penalty_created',
            sprintf(
                'Created %s penalty for %s.',
                $option['label'],
                $student->formatted_full_name
            ),
            $penalty,
            null,
            [
                'student_info_id' =>
                    $student->id,

                'student_name' =>
                    $student
                        ->formatted_full_name,

                'year' =>
                    $student->year,

                'penalty_code' =>
                    $validated[
                        'penalty_code'
                    ],

                'penalty_type' =>
                    $option[
                        'penalty_type'
                    ],

                'penalty_label' =>
                    $option[
                        'label'
                    ],

                'amount' =>
                    $option[
                        'amount'
                    ],

                'source' =>
                    'manual',
            ]
        );

        return response()->json([
            'message' =>
                'Penalty added successfully.',

            'data' => [
                'id' =>
                    $penalty->id,

                'penalty_type' =>
                    $penalty
                        ->penalty_type,

                'penalty_type_label' =>
                    $this->formatPenaltyType(
                        $penalty
                            ->penalty_type
                    ),

                'amount' =>
                    (float)
                    $penalty->amount,

                'source' =>
                    'other',

                'source_label' =>
                    'Manual / Other',

                'student' => [
                    'id' =>
                        $student->id,

                    'name' =>
                        $student
                            ->formatted_full_name,

                    'university_id' =>
                        $student
                            ->university_id,

                    'slmis_id' =>
                        $student
                            ->slmis_id,

                    'year' =>
                        $student->year,

                    'college' =>
                        $student
                            ->college
                            ?->college_name,

                    'program' =>
                        $student
                            ->program
                            ?->program_name,
                ],

                'reservation' =>
                    null,
            ],
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function attendancePenaltyTypes(): array
    {
        return [
            PictorialAttendanceService::PENALTY_TYPE_LATE,

            PictorialAttendanceService::PENALTY_TYPE_ABSENT,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Manual Penalty Catalog
    |--------------------------------------------------------------------------
    |
    | Keep the official manual penalty values here so the frontend cannot
    | submit arbitrary amounts.
    |
    | Rescheduling penalties are intentionally NOT included yet. Those will
    | be handled later through the student reservation management workflow.
    |
    */

    private function manualPenaltyOptions(): array
    {
        return [
            'pictorial_late_minor' => [
                'label' =>
                    'Late — 6 to 14 Minutes',

                'penalty_type' =>
                    PictorialAttendanceService::PENALTY_TYPE_LATE,

                'amount' =>
                    150.00,
            ],

            'pictorial_late_severe' => [
                'label' =>
                    'Late — 15 Minutes or More',

                'penalty_type' =>
                    PictorialAttendanceService::PENALTY_TYPE_LATE,

                'amount' =>
                    500.00,
            ],

            'pictorial_absent' => [
                'label' =>
                    'Absent / Missed Pictorial',

                'penalty_type' =>
                    PictorialAttendanceService::PENALTY_TYPE_ABSENT,

                'amount' =>
                    500.00,
            ],

            'pictorial_unauthorized_photo' => [
                'label' =>
                    'Unauthorized Taking of Photos',

                'penalty_type' =>
                    'pictorial_unauthorized_photo',

                'amount' =>
                    500.00,
            ],
        ];
    }

    private function formatPenaltyType(
        string $type
    ): string {
        return str($type)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}