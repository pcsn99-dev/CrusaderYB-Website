<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Year;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Support\AuditLogger;

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

        $year = DB::transaction(function () use ($validated) {
            $shouldActivate = (bool) ($validated['activate'] ?? false);

            if ($shouldActivate) {
                Year::query()->update([
                    'status' => false,
                ]);
            }

            return Year::create([
                'year' => $validated['year'],
                'theme' => $validated['theme'] ?? null,
                'subscription_start' => $validated['subscription_start'] ?? null,
                'subscription_end' => $validated['subscription_end'] ?? null,
                'status' => $shouldActivate,
            ]);
        });

        $adminName = auth()->user()->name ?? 'Unknown admin';

        AuditLogger::record(
            module: 'settings_years',
            action: 'year_created',
            description:
                "{$adminName} created CYB year {$year->year}".
                ($year->status ? ' and activated it.' : '.'),
            model: $year,
            oldValues: [],
            newValues: $year->toArray()
        );

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

        $oldValues = [
            'year' => $year->year,
            'theme' => $year->theme,
            'subscription_start' => $year->subscription_start,
            'subscription_end' => $year->subscription_end,
        ];

        $year->update([
            'year' => $validated['year'],
            'theme' => $validated['theme'] ?? null,
            'subscription_start' => $validated['subscription_start'] ?? null,
            'subscription_end' => $validated['subscription_end'] ?? null,
        ]);

        $year->refresh();

        $newValues = [
            'year' => $year->year,
            'theme' => $year->theme,
            'subscription_start' => $year->subscription_start,
            'subscription_end' => $year->subscription_end,
        ];

        $adminName = auth()->user()->name ?? 'Unknown admin';

        AuditLogger::record(
            module: 'settings_years',
            action: 'year_updated',
            description:
                "{$adminName} updated CYB year {$year->year}.",
            model: $year,
            oldValues: $oldValues,
            newValues: $newValues
        );

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

        $previousActiveYear = Year::active()->first();

        DB::transaction(function () use ($year) {
            Year::query()->update([
                'status' => false,
            ]);

            $year->update([
                'status' => true,
            ]);
        });

        $year->refresh();

        $adminName = auth()->user()->name ?? 'Unknown admin';

        AuditLogger::record(
            module: 'settings_years',
            action: 'year_activated',
            description:
                "{$adminName} activated CYB year {$year->year}".
                (
                    $previousActiveYear &&
                    $previousActiveYear->id !== $year->id
                        ? " and replaced CYB {$previousActiveYear->year} as the active year."
                        : '.'
                ),
            model: $year,
            oldValues: [
                'previous_active_year_id' =>
                    $previousActiveYear?->id,
                'previous_active_year' =>
                    $previousActiveYear?->year,
            ],
            newValues: [
                'active_year_id' => $year->id,
                'active_year' => $year->year,
            ]
        );

        return redirect()
            ->route('settings.years.index')
            ->with('success', "CYB {$year->year} is now the active year.");
    }
}