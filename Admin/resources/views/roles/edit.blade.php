<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Role
            </h2>

            <a href="{{ route('roles.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                Back to Roles
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $role->name }}
                        </h3>

                        <p class="text-sm text-gray-500">
                            Update role details and assigned permissions.
                        </p>

                        @if ($role->is_protected)
                            <div class="mt-3 rounded-md bg-blue-50 p-4 text-sm text-blue-700 border border-blue-200">
                                This is a protected system role. Its name and permissions are preserved to avoid system lockout.
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('roles.update', $role) }}">
                        @csrf
                        @method('PUT')

                        @include('roles.partials.form', [
                            'role' => $role,
                            'permissions' => $permissions,
                            'selectedPermissionIds' => old('permission_ids', $selectedPermissionIds),
                            'buttonText' => 'Update Role',
                        ])
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>