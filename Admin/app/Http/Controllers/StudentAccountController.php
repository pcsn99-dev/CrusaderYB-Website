<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\StudentInfo;
use Illuminate\Http\Request;

class StudentAccountController extends Controller
{
    public function index()
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
}