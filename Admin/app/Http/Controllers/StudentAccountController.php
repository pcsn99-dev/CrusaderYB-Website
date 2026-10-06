<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\StudentInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        /*
         * blocks returning full list of students when no search term or filters are provided
         */

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

                /*
                 * Split a search into separate terms. 
                 * Each term may match the ID or any part of
                 * the student's name.
                 */

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
}