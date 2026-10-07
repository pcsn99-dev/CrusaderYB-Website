<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\StudentInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeYear = DB::table('years')
            ->where('status', 1)
            ->first();

        $stats = [
            'student_accounts' => 0,
            'subscribed_students' => 0,
            'third_party_students' => 0,
            'reservations' => 0,
        ];

        if ($activeYear) {
            $year = $activeYear->year;

            $stats['student_accounts'] = StudentInfo::query()
                ->where('year', $year)
                ->count();

            $stats['subscribed_students'] = StudentInfo::query()
                ->where('year', $year)
                ->where('is_subscribe', true)
                ->count();

            $stats['third_party_students'] = StudentInfo::query()
                ->where('year', $year)
                ->where('is_subscribe', true)
                ->where('is_third_party', true)
                ->count();

            $stats['reservations'] = Reservation::query()
                ->whereHas('pictorial', function ($query) use ($year) {
                    $query->where('year', $year);
                })
                ->count();
        }

        return view('dashboard', [
            'activeYear' => $activeYear,
            'stats' => $stats,
        ]);
    }
}