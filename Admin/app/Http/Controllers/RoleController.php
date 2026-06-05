<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    // controller methods for views
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->orderBy('name')
            ->paginate(10);
        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('name')->get();
        return view('roles.create', compact('permissions'));
    }

    public function show(Role $role): View
    {
        $role->load(['permissions', 'users']);
        return view('roles.show', compact('role'));
    }

        public function edit(Role $role): View
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('name')->get();
        $selectedPermissionIds = $role->permissions
            ->pluck('id')
            ->toArray();

        return view('roles.edit', compact(
            'role',
            'permissions',
            'selectedPermissionIds'

        ));
    }



    // controller methods for actions
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('roles', 'name'),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'permission_ids' => [
                'nullable',
                'array',
            ],
            'permission_ids.*' => [
                'integer',
                Rule::exists('permissions', 'id'),
            ],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'guard_name' => 'web',
            'description' => $validated['description'] ?? null,
            'is_protected' => false,
        ]);

        $role->permissions()->sync($validated['permission_ids'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }





    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'permission_ids' => [
                'nullable',
                'array',
            ],
            'permission_ids.*' => [
                'integer',
                Rule::exists('permissions', 'id'),
            ],
        ]);

        if ($role->is_protected) {
            $role->update([
                'description' => $validated['description'] ?? $role->description,
            ]);

            $allPermissionIds = Permission::pluck('id')->toArray();

            $role->permissions()->sync($allPermissionIds);

            return redirect()
                ->route('roles.index')
                ->with('success', 'Protected role updated. All permissions were preserved.');
        }

        $role->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permission_ids'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_protected) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'Protected roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'This role cannot be deleted because users are assigned to it.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}