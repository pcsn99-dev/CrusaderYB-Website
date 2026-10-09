<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Pictorial;
use App\Models\Year;
use Illuminate\View\View;

class PictorialScheduleController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('view-pictorial-schedules') ||
            $user?->hasPermission('manage-pictorial-schedules'),
            403
        );

        $activeYear = Year::active()->first();

        $colleges = College::query()
            ->orderBy('college_name')
            ->get();

        $pictorials = Pictorial::query()
            ->with([
                'college',
                'allowedColleges',
            ])
            ->withCount([
                'activeReservations as reserved_slots',
            ])
            ->when(
                $activeYear,
                fn ($query) => $query->forYear($activeYear->year)
            )
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(25);

        return view(
            'settings.pictorial-schedules.index',
            compact(
                'activeYear',
                'colleges',
                'pictorials'
            )
        );
    }
}