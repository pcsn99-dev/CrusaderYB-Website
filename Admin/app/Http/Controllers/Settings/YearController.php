<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Year;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class YearController extends Controller
{
    public function index(): View
    {
        $years = Year::query()
            ->orderByDesc('year')
            ->get();

        return view('settings.years.index', compact('years'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => [
                'required',
                'string',
                'max:191',
                Rule::unique('years', 'year'),
            ],
            'theme' => [
                'nullable',
                'string',
                'max:191',
            ],
            'subscription_start' => [
                'nullable',
                'date',
            ],
            'subscription_end' => [
                'nullable',
                'date',
                'after_or_equal:subscription_start',
            ],
            'activate' => [
                'nullable',
                'boolean',
            ],
        ]);

        DB::transaction(function () use ($validated) {
            $shouldActivate = (bool) ($validated['activate'] ?? false);

            if ($shouldActivate) {
                Year::query()->update([
                    'status' => false,
                ]);
            }

            Year::create([
                'year' => $validated['year'],
                'theme' => $validated['theme'] ?? null,
                'subscription_start' => $validated['subscription_start'] ?? null,
                'subscription_end' => $validated['subscription_end'] ?? null,
                'status' => $shouldActivate,
            ]);
        });

        return redirect()
            ->route('settings.years.index')
            ->with('success', 'CYB year created successfully.');
    }

    public function update(
        Request $request,
        Year $year
    ): RedirectResponse {
        $validated = $request->validate([
            'year' => [
                'required',
                'string',
                'max:191',
                Rule::unique('years', 'year')->ignore($year->id),
            ],
            'theme' => [
                'nullable',
                'string',
                'max:191',
            ],
            'subscription_start' => [
                'nullable',
                'date',
            ],
            'subscription_end' => [
                'nullable',
                'date',
                'after_or_equal:subscription_start',
            ],
        ]);

        $year->update([
            'year' => $validated['year'],
            'theme' => $validated['theme'] ?? null,
            'subscription_start' => $validated['subscription_start'] ?? null,
            'subscription_end' => $validated['subscription_end'] ?? null,
        ]);

        return redirect()
            ->route('settings.years.index')
            ->with('success', 'CYB year updated successfully.');
    }

    public function activate(Year $year): RedirectResponse
    {
        if ($year->status) {
            return redirect()
                ->route('settings.years.index')
                ->with('info', "{$year->year} is already the active CYB year.");
        }

        DB::transaction(function () use ($year) {
            Year::query()->update([
                'status' => false,
            ]);

            $year->update([
                'status' => true,
            ]);
        });

        return redirect()
            ->route('settings.years.index')
            ->with('success', "CYB {$year->year} is now the active year.");
    }
}