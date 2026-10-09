<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Pictorial;
use App\Models\Year;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        return view(
            'settings.pictorial-schedules.index',
            compact(
                'activeYear',
                'colleges'
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

    private function authorizeAccess(): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('view-pictorial-schedules') ||
            $user?->hasPermission('manage-pictorial-schedules'),
            403
        );
    }
}