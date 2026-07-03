<?php

namespace App\Http\Controllers;

use App\Models\College;
use App\Models\GenericWriteup;
use Illuminate\Http\Request;

class GenericWriteupController extends Controller
{
    public function index(Request $request)
    {
        $years = GenericWriteup::query()
            ->select('year')
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
            ->when($selectedYear, function ($query) use ($selectedYear) {
                $query->where('year', $selectedYear);
            })
            ->when($selectedCollegeId, function ($query) use ($selectedCollegeId) {
                $query->where('college_id', $selectedCollegeId);
            })
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('writeups.generic.index', compact(
            'years',
            'colleges',
            'selectedYear',
            'selectedCollegeId',
            'genericWriteups'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'string', 'max:20'],
            'college_id' => ['required', 'exists:colleges,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'display_order' => ['nullable', 'integer', 'min:1', 'max:20'],
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

        GenericWriteup::create([
            'year' => $validated['year'],
            'college_id' => $validated['college_id'],
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'display_order' => $validated['display_order'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Generic writeup created successfully.');
    }

    public function update(Request $request, GenericWriteup $genericWriteup)
    {
        $validated = $request->validate([
            'year' => ['required', 'string', 'max:20'],
            'college_id' => ['required', 'exists:colleges,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'display_order' => ['nullable', 'integer', 'min:1', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $genericWriteup->update([
            'year' => $validated['year'],
            'college_id' => $validated['college_id'],
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'display_order' => $validated['display_order'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Generic writeup updated successfully.');
    }

    public function destroy(GenericWriteup $genericWriteup)
    {
        $genericWriteup->delete();

        return back()->with('success', 'Generic writeup deleted successfully.');
    }
}