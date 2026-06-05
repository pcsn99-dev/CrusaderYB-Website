<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admin Users
            </h2>

            <a href="{{ route('admin-users.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Create Admin User
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
                        <h3 class="text-lg font-semibold text-gray-900">
                            Admin Accounts
                        </h3>
                        <p class="text-sm text-gray-500">
                            Manage staff accounts, roles, account status, and temporary password resets.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                        Username
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                        Password
                                    </th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($adminUsers as $adminUser)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">
                                                {{ $adminUser->name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $adminUser->email }}
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-gray-600">
                                            <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                                {{ $adminUser->username ?: 'N/A' }}
                                            </code>
                                        </td>

                                        <td class="px-4 py-3 text-gray-600">
                                            @if ($adminUser->role)
                                                {{ $adminUser->role->name }}
                                            @else
                                                <span class="text-red-600">No role</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3">
                                            @if ($adminUser->active)
                                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 border border-green-200">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 border border-red-200">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3">
                                            @if ($adminUser->must_change_password)
                                                <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-700 border border-yellow-200">
                                                    Must change
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-700 border border-gray-200">
                                                    Set
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('admin-users.show', $adminUser) }}"
                                                   class="text-sm text-gray-600 hover:text-gray-900">
                                                    View
                                                </a>

                                                <a href="{{ route('admin-users.edit', $adminUser) }}"
                                                   class="text-sm text-blue-600 hover:text-blue-900">
                                                    Edit
                                                </a>

                                                @if ($adminUser->active)
                                                    <form method="POST"
                                                          action="{{ route('admin-users.deactivate', $adminUser) }}"
                                                          onsubmit="return confirm('Deactivate this admin account?');">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button type="submit"
                                                                class="text-sm text-orange-600 hover:text-orange-900">
                                                            Deactivate
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST"
                                                          action="{{ route('admin-users.activate', $adminUser) }}"
                                                          onsubmit="return confirm('Activate this admin account?');">
                                                        @csrf
                                                        @method('PATCH')

                                                        <button type="submit"
                                                                class="text-sm text-green-600 hover:text-green-900">
                                                            Activate
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST"
                                                      action="{{ route('admin-users.destroy', $adminUser) }}"
                                                      onsubmit="return confirm('Are you sure you want to delete this admin user?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="text-sm text-red-600 hover:text-red-900">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                            No admin users found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $adminUsers->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>