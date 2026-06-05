<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Notifications\TemporaryPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $adminUsers = User::with('role')
            ->where('type', 'admin')
            ->orderBy('name')
            ->paginate(10);
        return view('admin-users.index', compact('adminUsers'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        return view('admin-users.create', compact('roles'));
    }

    public function show(User $adminUser): View
    {
        $this->ensureAdminUser($adminUser);
        $adminUser->load('role');

        return view('admin-users.show', compact('adminUser'));
    }

    public function edit(User $adminUser): View
    {
        $this->ensureAdminUser($adminUser);
        $roles = Role::orderBy('name')->get();
        return view('admin-users.edit', compact('adminUser', 'roles'));
    }







    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email'),
            ],
            'username' => [
                'required',
                'string',
                'max:191',
                Rule::unique('users', 'username'),
            ],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $temporaryPassword = $this->generateTemporaryPassword();
        $adminUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'role_id' => $validated['role_id'],
            'password' => Hash::make($temporaryPassword),
            'type' => 'admin',
            'active' => $request->boolean('active', true),
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->addDays(3),
            'email_verified_at' => now(),
        ]);

        $adminUser->notify(new TemporaryPasswordNotification($temporaryPassword));

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'Admin user created successfully. A temporary password was sent to their email.');
    }

    public function update(Request $request, User $adminUser): RedirectResponse
    {
        $this->ensureAdminUser($adminUser);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($adminUser->id),
            ],
            'username' => [
                'required',
                'string',
                'max:191',
                Rule::unique('users', 'username')->ignore($adminUser->id),
            ],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id'),
            ],
            'active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $adminUser->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'role_id' => $validated['role_id'],
            'active' => $request->boolean('active'),
            'type' => 'admin',
        ]);

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'Admin user updated successfully.');
    }

    public function destroy(User $adminUser): RedirectResponse
    {
        $this->ensureAdminUser($adminUser);

        if (auth()->id() === $adminUser->id) {
            return redirect()
                ->route('admin-users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($adminUser->hasRole('super-admin')) {
            return redirect()
                ->route('admin-users.index')
                ->with('error', 'Super Admin accounts cannot be deleted.');
        }

        $adminUser->delete();

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'Admin user deleted successfully.');
    }

    public function resendTemporaryPassword(User $adminUser): RedirectResponse
    {
        $this->ensureAdminUser($adminUser);

        if (auth()->id() === $adminUser->id) {
            return redirect()
                ->route('admin-users.show', $adminUser)
                ->with('error', 'You cannot reset your own password from this page.');
        }

        $temporaryPassword = $this->generateTemporaryPassword();

        $adminUser->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->addDays(3),
        ]);

        $adminUser->notify(new TemporaryPasswordNotification($temporaryPassword));

        return redirect()
            ->route('admin-users.show', $adminUser)
            ->with('success', 'A new temporary password was sent. The user must change their password on next login.');
    }

    public function deactivate(User $adminUser): RedirectResponse
    {
        $this->ensureAdminUser($adminUser);

        if (auth()->id() === $adminUser->id) {
            return redirect()
                ->route('admin-users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        if ($adminUser->hasRole('super-admin')) {
            return redirect()
                ->route('admin-users.index')
                ->with('error', 'Super Admin accounts cannot be deactivated.');
        }

        $adminUser->update([
            'active' => false,
        ]);

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'Admin user deactivated successfully.');
    }

    public function activate(User $adminUser): RedirectResponse
    {
        $this->ensureAdminUser($adminUser);

        $adminUser->update([
            'active' => true,
        ]);

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'Admin user activated successfully.');
    }

    private function generateTemporaryPassword(): string
    {
        return Str::password(
            length: 12,
            letters: true,
            numbers: true,
            symbols: true,
            spaces: false
        );
    }

    private function ensureAdminUser(User $user): void
    {
        abort_if($user->type !== 'admin', 404);
    }
}