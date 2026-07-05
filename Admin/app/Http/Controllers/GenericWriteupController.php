<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\GenericWriteup;
use App\Models\StudentInfo;
use Illuminate\Http\Request;
use App\Support\AuditLogger;

class GenericWriteupController extends Controller
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

        $genericWriteups = GenericWriteup::query()
            ->with(['college', 'creator', 'updater'])
            ->when($selectedYear, fn ($query) =>
                $query->where('year', $selectedYear)
            )
            ->when($selectedCollegeId, fn ($query) =>
                $query->where('college_id', $selectedCollegeId)
            )
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $currentCount = GenericWriteup::query()
            ->when($selectedYear, fn ($query) =>
                $query->where('year', $selectedYear)
            )
            ->when($selectedCollegeId, fn ($query) =>
                $query->where('college_id', $selectedCollegeId)
            )
            ->count();

        return view('writeups.generic.index', compact(
            'years',
            'colleges',
            'selectedYear',
            'selectedCollegeId',
            'genericWriteups',
            'currentCount'
        ));
    }








    public function store(Request $request)
    {

        $validated = $request->validate([
            'year' => ['required', 'string', 'max:20'],
            'college_id' => ['required', 'exists:colleges,id'],
            'content' => ['required', 'string', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $existingCount = GenericWriteup::query()
            ->where('year', $validated['year'])
            ->where('college_id', $validated['college_id'])
            ->count();

        if ($existingCount >= 20) {
            return back()
                ->withInput()
                ->withErrors([
                    'limit' => 'This college already has the maximum of 20 generic writeups for the selected school year.',
                ]);
        }

        $genericWriteup = GenericWriteup::create([
            'year' => $validated['year'],
            'college_id' => $validated['college_id'],
            'content' => $validated['content'],
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $genericWriteup->load(['college', 'creator', 'updater']);

        AuditLogger::record(
            module: 'generic_writeups',
            action: 'created',
            description: "Created generic writeup for {$genericWriteup->college?->college_name} - {$genericWriteup->year}",
            model: $genericWriteup,
            newValues: $genericWriteup->only([
                'id',
                'year',
                'college_id',
                'content',
                'is_active',
                'created_by',
                'updated_by',
            ])
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Generic writeup created successfully.',
                'genericWriteup' => $genericWriteup,
            ]);
        }

        return back()->with('success', 'Generic writeup created successfully.');

    }





    public function update(Request $request, GenericWriteup $genericWriteup)
    {
        $validated = $request->validate([
            'year' => ['required', 'string', 'max:20'],
            'college_id' => ['required', 'exists:colleges,id'],
            'content' => ['required', 'string', 'max:300'],
            'is_active' => ['nullable', 'boolean'],
        ]);


        $genericWriteup->load('college');

        $oldValues = $genericWriteup->only([
            'year',
            'college_id',
            'content',
            'is_active',
            'updated_by',
        ]);

        $genericWriteup->update([
            'year' => $validated['year'],
            'college_id' => $validated['college_id'],
            'content' => $validated['content'],
            'is_active' => $request->boolean('is_active'),
            'updated_by' => auth()->id(),
        ]);

        $genericWriteup->load(['college', 'creator', 'updater']);

        AuditLogger::record(
            module: 'generic_writeups',
            action: 'updated',
            description: "Updated generic writeup for {$genericWriteup->college?->college_name} - {$genericWriteup->year}",
            model: $genericWriteup,
            oldValues: $oldValues,
            newValues: $genericWriteup->only([
                'year',
                'college_id',
                'content',
                'is_active',
                'updated_by',
            ])
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Generic writeup updated successfully.',
                'genericWriteup' => $genericWriteup,
            ]);
        }

        return back()->with('success', 'Generic writeup updated successfully.');


    }

    public function destroy(Request $request, GenericWriteup $genericWriteup)
    {
        $genericWriteup->load('college');

        $oldValues = $genericWriteup->only([
            'id',
            'year',
            'college_id',
            'content',
            'is_active',
            'created_by',
            'updated_by',
        ]);

        $description = "Deleted generic writeup for {$genericWriteup->college?->college_name} - {$genericWriteup->year}";

        $genericWriteup->delete();

        AuditLogger::record(
            module: 'generic_writeups',
            action: 'deleted',
            description: $description,
            model: $genericWriteup,
            oldValues: $oldValues
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Generic writeup deleted successfully.',
            ]);
        }

        return back()->with('success', 'Generic writeup deleted successfully.');
    }
}