<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Role Management
            </h2>

            <a href="{{ route('roles.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Create Role
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Roles</h3>
                        <p class="text-sm text-gray-500">
                            Manage admin roles and assign permissions for each role.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                        Slug
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                        Permissions
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                        Users
                                    </th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($roles as $role)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">
                                                {{ $role->name }}

                                                @if ($role->is_protected)
                                                    <span class="ml-2 inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 border border-blue-200">
                                                        Protected
                                                    </span>
                                                @endif
                                            </div>

                                            @if ($role->description)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $role->description }}
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-gray-600">
                                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                                {{ $role->slug }}
                                            </code>
                                        </td>

                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $role->permissions_count }}
                                        </td>

                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $role->users_count }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('roles.show', $role) }}"
                                                   class="text-sm text-gray-600 hover:text-gray-900">
                                                    View
                                                </a>

                                                <a href="{{ route('roles.edit', $role) }}"
                                                   class="text-sm text-blue-600 hover:text-blue-900">
                                                    Edit
                                                </a>

                                                <form action="{{ route('roles.destroy', $role) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="text-sm text-red-600 hover:text-red-900 disabled:text-gray-400"
                                                            @disabled($role->is_protected || $role->users_count > 0)>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                            No roles found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $roles->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>