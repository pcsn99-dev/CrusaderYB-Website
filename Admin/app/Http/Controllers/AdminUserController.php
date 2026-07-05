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
use App\Support\AuditLogger;

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

        AuditLogger::record(
            module: 'admin_users',
            action: 'created',
            description: "Created admin user: {$adminUser->name}",
            model: $adminUser,
            newValues: $adminUser->only([
                'id',
                'name',
                'email',
                'username',
                'role_id',
                'type',
                'active',
                'must_change_password',
                'temporary_password_expires_at',
            ])
        );

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

        $oldValues = $adminUser->only([
            'name',
            'email',
            'username',
            'role_id',
            'active',
        ]);

        $adminUser->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'role_id' => $validated['role_id'],
            'active' => $request->boolean('active'),
            'type' => 'admin',
        ]);

        $adminUser->refresh();

        AuditLogger::record(
            module: 'admin_users',
            action: 'updated',
            description: "Updated admin user: {$adminUser->name}",
            model: $adminUser,
            oldValues: $oldValues,
            newValues: $adminUser->only([
                'name',
                'email',
                'username',
                'role_id',
                'active',
            ])
        );

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

        $oldValues = $adminUser->only([
            'id',
            'name',
            'email',
            'username',
            'role_id',
            'active',
        ]);

        $adminUser->delete();

        AuditLogger::record(
            module: 'admin_users',
            action: 'deleted',
            description: "Deleted admin user: {$oldValues['name']}",
            model: $adminUser,
            oldValues: $oldValues
        );

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

        $oldValues = $adminUser->only([
            'must_change_password',
            'temporary_password_expires_at',
        ]);

        $adminUser->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
            'temporary_password_expires_at' => now()->addDays(3),
        ]);

        $adminUser->notify(new TemporaryPasswordNotification($temporaryPassword));

        $adminUser->refresh();

        AuditLogger::record(
            module: 'admin_users',
            action: 'temporary_password_resent',
            description: "Resent temporary password to admin user: {$adminUser->name}",
            model: $adminUser,
            oldValues: $oldValues,
            newValues: $adminUser->only([
                'must_change_password',
                'temporary_password_expires_at',
            ])
        );

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

        $oldValues = $adminUser->only(['active']);

        $adminUser->update([
            'active' => false,
        ]);

        $adminUser->refresh();

        AuditLogger::record(
            module: 'admin_users',
            action: 'deactivated',
            description: "Deactivated admin user: {$adminUser->name}",
            model: $adminUser,
            oldValues: $oldValues,
            newValues: $adminUser->only(['active'])
        );

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'Admin user deactivated successfully.');
    }

    public function activate(User $adminUser): RedirectResponse
    {
        $this->ensureAdminUser($adminUser);

        $oldValues = $adminUser->only(['active']);

        $adminUser->update([
            'active' => true,
        ]);

        $adminUser->refresh();

        AuditLogger::record(
            module: 'admin_users',
            action: 'activated',
            description: "Activated admin user: {$adminUser->name}",
            model: $adminUser,
            oldValues: $oldValues,
            newValues: $adminUser->only(['active'])
        );

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