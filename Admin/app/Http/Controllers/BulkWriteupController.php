<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\GenericWriteup;
use App\Models\StudentInfo;
use App\Models\Writeup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkWriteupController extends Controller
{
    public function index(Request $request)
    {
        $years = StudentInfo::query()
            ->select('year')
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $colleges = College::query()
            ->orderBy('college_name')
            ->get();

        $selectedYear = $request->input('year');
        $selectedCollegeId = $request->input('college_id');

        $students = collect();
        $studentCount = 0;
        $genericWriteupCount = 0;
        $selectedCollege = null;

        if ($selectedYear && $selectedCollegeId) {
            $selectedCollege = College::find($selectedCollegeId);

            $students = $this->missingWriteupStudents($selectedYear, $selectedCollegeId)->get();
            $studentCount = $students->count();

            $genericWriteupCount = GenericWriteup::query()
                ->where('year', $selectedYear)
                ->where('college_id', $selectedCollegeId)
                ->where('is_active', true)
                ->count();
        }

        return view('writeups.bulk.index', compact(
            'years',
            'colleges',
            'selectedYear',
            'selectedCollegeId',
            'selectedCollege',
            'students',
            'studentCount',
            'genericWriteupCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'string', 'max:20'],
            'college_id' => ['required', 'exists:colleges,id'],
        ]);

        $genericWriteups = GenericWriteup::query()
            ->where('year', $validated['year'])
            ->where('college_id', $validated['college_id'])
            ->where('is_active', true)
            ->pluck('content');

        if ($genericWriteups->isEmpty()) {
            return back()->withErrors([
                'generic_writeups' => 'No active generic writeups found for this year and college.',
            ]);
        }

        $students = $this->missingWriteupStudents(
            $validated['year'],
            $validated['college_id']
        )->get();

        if ($students->isEmpty()) {
            return back()->withErrors([
                'students' => 'No students found without writeups for this year and college.',
            ]);
        }

        $createdCount = DB::transaction(function () use ($students, $genericWriteups) {
            $createdCount = 0;

            foreach ($students->shuffle()->values() as $student) {
                $alreadyHasWriteup = Writeup::query()
                    ->where('student_info_id', $student->id)
                    ->exists();

                if ($alreadyHasWriteup) {
                    continue;
                }

                Writeup::create([
                    'student_info_id' => $student->id,
                    'writeup' => $genericWriteups->random(),
                    'edited_writeup' => null,
                    'proofreader_id' => null,
                    'is_done' => 0,
                    'review_status' => 'pending',
                    'is_flagged' => 0,
                    'date_of_proofread' => null,
                ]);

                $createdCount++;
            }

            return $createdCount;
        });

        return redirect()
            ->route('writeups.bulk.index', [
                'year' => $validated['year'],
                'college_id' => $validated['college_id'],
            ])
            ->with('success', "{$createdCount} missing writeups were created successfully.");
    }

    private function missingWriteupStudents($year, $collegeId)
    {
        return StudentInfo::query()
            ->with(['college', 'program', 'major'])
            ->where('year', $year)
            ->where('college_id', $collegeId)
            ->where('is_subscribe', 1)
            ->whereDoesntHave('writeup')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('suffix');
    }
}