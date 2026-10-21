<?php

namespace App\Http\Controllers;

use App\Models\Penalty;
use App\Models\Year;
use App\Services\PictorialAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        |
        | Penalties are scoped using student_info.year.
        |
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
                function ($studentQuery) use (
                    $validated
                ) {
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
                function ($studentQuery) use (
                    $search
                ) {
                    $studentQuery->where(
                        function ($searchQuery) use (
                            $search
                        ) {
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
        | Penalty Source Filter
        |--------------------------------------------------------------------------
        */

        $attendanceTypes = [
            PictorialAttendanceService::PENALTY_TYPE_LATE,
            PictorialAttendanceService::PENALTY_TYPE_ABSENT,
        ];

        if (
            ($validated['source'] ?? null) ===
            'attendance'
        ) {
            $query->whereIn(
                'penalty_type',
                $attendanceTypes
            );
        }

        if (
            ($validated['source'] ?? null) ===
            'other'
        ) {
            $query->whereNotIn(
                'penalty_type',
                $attendanceTypes
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        |
        | These totals respect the currently selected year, search term and
        | filters. This means filtering to "Pictorial Late", for example,
        | will also update the displayed amount and record count.
        |
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
                                $student?->year,

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
        |
        | Only penalty types that exist within the selected CYB year are
        | returned. This prevents unrelated historical types from cluttering
        | the filter.
        |
        */

        $penaltyTypes = Penalty::query()
            ->whereHas(
                'studentInfo',
                function ($studentQuery) use (
                    $validated
                ) {
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
                    'value' => $type,

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
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function formatPenaltyType(
        string $type
    ): string {
        return str($type)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
}