@php
    $selectedPermissionIds = collect($selectedPermissionIds ?? [])->map(fn ($id) => (int) $id)->toArray();

    $systemPermissions = $permissions->filter(function ($permission) {
        return str_contains($permission->slug, 'dashboard')
            || str_contains($permission->slug, 'roles')
            || str_contains($permission->slug, 'admin-users');
    });

    $writeupPermissions = $permissions->filter(function ($permission) {
        return str_contains($permission->slug, 'writeups');
    });

    $otherPermissions = $permissions->reject(function ($permission) use ($systemPermissions, $writeupPermissions) {
        return $systemPermissions->contains('id', $permission->id)
            || $writeupPermissions->contains('id', $permission->id);
    });

    $permissionGroups = [
        'System Management' => $systemPermissions,
        'Writeup Workflow' => $writeupPermissions,
        'Other Permissions' => $otherPermissions,
    ];
@endphp

<div class="space-y-6">

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">
            Role Name
        </label>

        <input type="text"
               name="name"
               id="name"
               value="{{ old('name', $role?->name) }}"
               @readonly($role?->is_protected)
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 read-only:bg-gray-100"
               placeholder="Example: Editorial Staff">

        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @if ($role?->is_protected)
            <p class="mt-2 text-xs text-gray-500">
                Protected role names cannot be changed.
            </p>
        @endif
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">
            Description
        </label>

        <textarea name="description"
                  id="description"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Briefly describe what this role is allowed to do.">{{ old('description', $role?->description) }}</textarea>

        @error('description')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-3">
            <div>
                <h4 class="text-sm font-semibold text-gray-900">
                    Permissions
                </h4>
                <p class="text-sm text-gray-500">
                    Select the permissions this role should have.
                </p>
            </div>

            @if (! $role?->is_protected)
                <button type="button"
                        onclick="toggleAllPermissions(true)"
                        class="text-xs text-blue-600 hover:text-blue-900">
                    Select all
                </button>
            @endif
        </div>

        @error('permission_ids')
            <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="space-y-4">
            @foreach ($permissionGroups as $groupName => $groupPermissions)
                @if ($groupPermissions->count())
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h5 class="text-sm font-semibold text-gray-800">
                                {{ $groupName }}
                            </h5>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach ($groupPermissions as $permission)
                                <label class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox"
                                           name="permission_ids[]"
                                           value="{{ $permission->id }}"
                                           class="permission-checkbox mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                           @checked(in_array($permission->id, $selectedPermissionIds))
                                           @disabled($role?->is_protected)>

                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $permission->name }}
                                        </div>

                                        <div class="mt-1">
                                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                                {{ $permission->slug }}
                                            </code>
                                        </div>

                                        @if ($permission->description)
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $permission->description }}
                                            </p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @if ($role?->is_protected)
            <p class="mt-3 text-xs text-gray-500">
                Protected roles automatically keep all permissions.
            </p>
        @endif
    </div>

    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
        <a href="{{ route('roles.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
            Cancel
        </a>

        <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            {{ $buttonText }}
        </button>
    </div>
</div>

<script>
    function toggleAllPermissions(checked) {
        document.querySelectorAll('.permission-checkbox').forEach(function (checkbox) {
            if (!checkbox.disabled) {
                checkbox.checked = checked;
            }
        });
    }
</script>