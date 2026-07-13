<?php

namespace App\Http\Controllers;

use App\Models\BulkWriteupBatch;
use App\Models\College;
use App\Models\GenericWriteup;
use App\Models\StudentInfo;
use App\Models\Writeup;
use App\Support\AuditLogger;
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
        $bulkBatches = collect();

        $studentCount = 0;
        $genericWriteupCount = 0;
        $selectedCollege = null;

        if ($selectedYear && $selectedCollegeId) {
            $selectedCollege = College::find($selectedCollegeId);

            $students = $this->missingWriteupStudents(
                $selectedYear,
                $selectedCollegeId
            )->get();

            $studentCount = $students->count();

            $genericWriteupCount = GenericWriteup::query()
                ->where('year', $selectedYear)
                ->where('college_id', $selectedCollegeId)
                ->where('is_active', true)
                ->count();

            $bulkBatches = BulkWriteupBatch::query()
                ->with([
                    'college',
                    'creator',
                    'undoneBy',
                ])
                ->withCount([
                    'writeups as undoable_writeups_count',
                    'writeupsWithTrashed as linked_writeups_total_count',
                ])
                ->where('year', $selectedYear)
                ->where('college_id', $selectedCollegeId)
                ->latest()
                ->get();
        }

        return view('writeups.bulk.index', compact(
            'years',
            'colleges',
            'selectedYear',
            'selectedCollegeId',
            'selectedCollege',
            'students',
            'studentCount',
            'genericWriteupCount',
            'bulkBatches'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'string', 'max:20'],
            'college_id' => ['required', 'exists:colleges,id'],
        ]);

        /*
         * Retrieve the complete GenericWriteup models instead of
         * plucking only their content. We need each template's ID.
         */
        $genericWriteups = GenericWriteup::query()
            ->where('year', $validated['year'])
            ->where('college_id', $validated['college_id'])
            ->where('is_active', true)
            ->get();

        if ($genericWriteups->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'generic_writeups' =>
                        'No active generic writeups found for this year and college.',
                ]);
        }

        $students = $this->missingWriteupStudents(
            $validated['year'],
            $validated['college_id']
        )->get();

        /*
         * This must happen before creating the batch.
         * Otherwise, an empty batch record would be stored.
         */
        if ($students->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors([
                    'students' =>
                        'No students found without writeups for this year and college.',
                ]);
        }

        $reviewerId = auth()->id();

        [$batch, $createdCount] = DB::transaction(
            function () use (
                $validated,
                $students,
                $genericWriteups,
                $reviewerId
            ) {
                $batch = BulkWriteupBatch::create([
                    'year' => $validated['year'],
                    'college_id' => $validated['college_id'],
                    'created_by' => $reviewerId,
                    'created_count' => 0,
                ]);

                $createdCount = 0;

                foreach ($students->shuffle()->values() as $student) {
                    /*
                     * Recheck inside the transaction in case another
                     * process created a writeup after the page loaded.
                     */
                    $alreadyHasWriteup = Writeup::query()
                        ->where('student_info_id', $student->id)
                        ->exists();

                    if ($alreadyHasWriteup) {
                        continue;
                    }

                    /** @var GenericWriteup $genericWriteup */
                    $genericWriteup = $genericWriteups->random();

                    Writeup::create([
                        'student_info_id' => $student->id,

                        /*
                         * Track both the exact generic template and
                         * the exact bulk operation.
                         */
                        'generic_writeup_id' => $genericWriteup->id,
                        'bulk_writeup_batch_id' => $batch->id,

                        'writeup' => $genericWriteup->content,
                        'edited_writeup' => $genericWriteup->content,

                        /*
                         * Generic writeups are already approved, so
                         * generated writeups begin as reviewed.
                         */
                        'proofreader_id' => $reviewerId,
                        'is_done' => true,
                        'review_status' => 'reviewed',
                        'is_flagged' => false,
                        'date_of_proofread' => now()->toDateString(),
                        'reviewed_at' => now(),

                        'locked_by' => null,
                        'locked_at' => null,
                    ]);

                    $createdCount++;
                }

                /*
                 * This can happen only if another bulk process created
                 * all the records during this request.
                 */
                if ($createdCount === 0) {
                    $batch->delete();

                    return [null, 0];
                }

                $batch->update([
                    'created_count' => $createdCount,
                ]);

                return [
                    $batch->fresh(),
                    $createdCount,
                ];
            }
        );

        if (! $batch || $createdCount === 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'students' =>
                        'No writeups were created. The eligible students may already have writeups.',
                ]);
        }

        $selectedCollege = College::find($validated['college_id']);
        $reviewerName = auth()->user()?->name ?? 'Unknown admin';

        AuditLogger::record(
            module: 'bulk_writeups',
            action: 'bulk_created',
            description:
                "{$reviewerName} bulk created {$createdCount} reviewed writeups for ".
                "{$selectedCollege?->college_name} - {$validated['year']}.",
            model: $batch,
            newValues: [
                'batch_id' => $batch->id,
                'year' => $validated['year'],
                'college_id' => $validated['college_id'],
                'college_name' => $selectedCollege?->college_name,
                'eligible_student_count' => $students->count(),
                'active_generic_writeup_count' => $genericWriteups->count(),
                'created_count' => $createdCount,
                'created_by' => $reviewerId,
                'review_status' => 'reviewed',
            ]
        );

        return redirect()
            ->route('writeups.bulk.index', [
                'year' => $validated['year'],
                'college_id' => $validated['college_id'],
            ])
            ->with(
                'success',
                "{$createdCount} missing writeups were created and marked as reviewed."
            );
    }

    public function undo(BulkWriteupBatch $batch)
    {
        $undoResult = DB::transaction(function () use ($batch) {
            /*
             * Lock the batch so two Undo requests cannot process
             * the same operation simultaneously.
             */
            $lockedBatch = BulkWriteupBatch::query()
                ->lockForUpdate()
                ->findOrFail($batch->id);

            if ($lockedBatch->undone_at !== null) {
                return [
                    'status' => 'already_undone',
                    'batch' => $lockedBatch,
                    'removed_count' => 0,
                ];
            }

            /*
             * This relationship contains only writeups still connected
             * to the batch. Re-reviewed writeups will later have their
             * batch ID cleared and will therefore not appear here.
             */
            $writeupsToRemove = $lockedBatch->writeups()->get();

            if ($writeupsToRemove->isEmpty()) {
                return [
                    'status' => 'nothing_to_undo',
                    'batch' => $lockedBatch,
                    'removed_count' => 0,
                ];
            }

            $removedCount = $writeupsToRemove->count();

            $oldValues = $lockedBatch->only([
                'undone_by',
                'undone_count',
                'undone_at',
            ]);

            /*
             * Writeup uses SoftDeletes, so these records are recoverable
             * and remain available for audit/history purposes.
             */
            $writeupsToRemove->each(
                fn (Writeup $writeup) => $writeup->delete()
            );

            $lockedBatch->update([
                'undone_by' => auth()->id(),
                'undone_count' => $removedCount,
                'undone_at' => now(),
            ]);

            return [
                'status' => 'undone',
                'batch' => $lockedBatch->fresh([
                    'college',
                    'creator',
                    'undoneBy',
                ]),
                'removed_count' => $removedCount,
                'old_values' => $oldValues,
            ];
        });

        if ($undoResult['status'] === 'already_undone') {
            return back()->withErrors([
                'bulk_batch' => 'This bulk-create operation has already been undone.',
            ]);
        }

        if ($undoResult['status'] === 'nothing_to_undo') {
            return back()->withErrors([
                'bulk_batch' =>
                    'There are no remaining linked writeups to remove. They may already have been re-reviewed.',
            ]);
        }

        /** @var BulkWriteupBatch $updatedBatch */
        $updatedBatch = $undoResult['batch'];
        $removedCount = $undoResult['removed_count'];
        $protectedCount = max(
            0,
            $updatedBatch->created_count - $removedCount
        );

        $adminName = auth()->user()?->name ?? 'Unknown admin';
        $collegeName = $updatedBatch->college?->college_name
            ?? 'Unknown college';

        AuditLogger::record(
            module: 'bulk_writeups',
            action: 'bulk_create_undone',
            description:
                "{$adminName} undid bulk batch #{$updatedBatch->id}. ".
                "{$removedCount} writeups were removed and ".
                "{$protectedCount} re-reviewed writeups were preserved.",
            model: $updatedBatch,
            oldValues: $undoResult['old_values'],
            newValues: [
                'batch_id' => $updatedBatch->id,
                'year' => $updatedBatch->year,
                'college_id' => $updatedBatch->college_id,
                'college_name' => $collegeName,
                'original_created_count' => $updatedBatch->created_count,
                'removed_count' => $removedCount,
                'protected_count' => $protectedCount,
                'undone_by' => $updatedBatch->undone_by,
                'undone_at' => $updatedBatch->undone_at,
            ]
        );

        $routeParameters = [
            'year' => $updatedBatch->year,
        ];

        if ($updatedBatch->college_id !== null) {
            $routeParameters['college_id'] = $updatedBatch->college_id;
        }

        return redirect()
            ->route('writeups.bulk.index', $routeParameters)
            ->with(
                'success',
                "{$removedCount} bulk-created writeups were removed. ".
                "{$protectedCount} re-reviewed writeups were preserved."
            );
    }

    private function missingWriteupStudents($year, $collegeId)
    {
        return StudentInfo::query()
            ->with([
                'college',
                'program',
                'major',
            ])
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