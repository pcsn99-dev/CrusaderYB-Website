<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Role Details
            </h2>

            <a href="{{ route('roles.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                Back to Roles
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $role->name }}

                                @if ($role->is_protected)
                                    <span class="ml-2 inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 border border-blue-200">
                                        Protected
                                    </span>
                                @endif
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $role->description ?: 'No description provided.' }}
                            </p>

                            <div class="mt-3 text-sm text-gray-600">
                                Slug:
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                    {{ $role->slug }}
                                </code>
                            </div>
                        </div>

                        <a href="{{ route('roles.edit', $role) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Edit Role
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Permissions
                    </h3>

                    @if ($role->permissions->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach ($role->permissions as $permission)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="font-medium text-gray-900">
                                        {{ $permission->name }}
                                    </div>

                                    <div class="mt-1">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                            {{ $permission->slug }}
                                        </code>
                                    </div>

                                    @if ($permission->description)
                                        <p class="text-sm text-gray-500 mt-2">
                                            {{ $permission->description }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">
                            No permissions assigned to this role.
                        </p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Assigned Users
                    </h3>

                    @if ($role->users->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($role->users as $user)
                                        <tr>
                                            <td class="px-4 py-3 text-gray-900">
                                                {{ $user->name }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $user->email }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ ucfirst($user->type ?? 'N/A') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">
                            No users are assigned to this role.
                        </p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>