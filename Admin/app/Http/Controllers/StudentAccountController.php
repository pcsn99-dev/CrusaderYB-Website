<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\StudentInfo;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
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

        $reservations = $student->reservations
            ->sortByDesc(function ($reservation) {
                return $reservation->pictorial?->date ?? '';
            })
            ->values()
            ->map(function ($reservation) {
                $attendanceStatus = match ($reservation->is_present) {
                    1 => 'present',
                    0 => 'absent',
                    2 => 'late',
                    default => 'not-recorded',
                };

                return [
                    'id' => $reservation->id,
                    'is_rescheduled' => (bool) $reservation->is_reschedule,
                    'reschedule_date' => $reservation->reschedule_date,
                    'is_present_date' => $reservation->is_present_date,
                    'attendance_status' => $attendanceStatus,
                    'created_at' => $reservation->created_at,
                    'pictorial' => $reservation->pictorial
                        ? [
                            'id' => $reservation->pictorial->id,
                            'date' => $reservation->pictorial->date,
                            'start_time' => $reservation->pictorial->start_time,
                            'end_time' => $reservation->pictorial->end_time,
                        ]
                        : null,
                ];
            });

        $studentData = [
            'id' => $student->id,
            'university_id' => $student->university_id,
            'slmis_id' => $student->slmis_id,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'suffix' => $student->suffix,
            'full_name' => $student->formatted_full_name,

            'email' => $student->user?->email,
            'contact_number' => $student->contact_number,
            'current_address' => $student->current_address,
            'permanent_address' => $student->permanent_address,

            'graduation_year' => $student->year,
            'expected_graduation_date' => $student->expected_graduation_date,

            'college' => $student->college?->college_name,
            'program' => $student->program?->program_name,
            'major' => $student->major?->major_name,

            'is_agree_contract' => (bool) $student->is_agree_contract,
            'is_subscribe' => (bool) $student->is_subscribe,
            'is_third_party' => (bool) $student->is_third_party,

            'subscribe_date' => $student->subscribe_date?->format('Y-m-d'),
            'unsubscribe_date' => $student->unsubscribe_date?->format('Y-m-d'),

            'claim_pic' => (bool) $student->claim_pic,
            'claim_pic_date' => $student->claim_pic_date?->format('Y-m-d'),

            'reservations' => $reservations,

            'permissions' => [
                'manage_subscription' => Auth::user()?->hasPermission(
                    'manage-student-subscription'
                ) ?? false,

                'manage_third_party' => Auth::user()?->hasPermission(
                    'manage-third-party-status'
                ) ?? false,
            ],
        ];

        return view('student-accounts.show', [
            'student' => $studentData,
        ]);
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

}