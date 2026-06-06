<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Role
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
                            New Role
                        </h3>
                        <p class="text-sm text-gray-500">
                            Create a role and select the permissions that should belong to it.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('roles.store') }}">
                        @csrf

                        @include('roles.partials.form', [
                            'role' => null,
                            'permissions' => $permissions,
                            'selectedPermissionIds' => old('permission_ids', []),
                            'buttonText' => 'Create Role',
                        ])
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>