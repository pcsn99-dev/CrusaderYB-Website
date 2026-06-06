<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admin User Details
            </h2>

            <a href="{{ route('admin-users.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                Back to Admin Users
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $adminUser->name }}
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                {{ $adminUser->email }}
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($adminUser->active)
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 border border-green-200">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 border border-red-200">
                                        Inactive
                                    </span>
                                @endif

                                @if ($adminUser->must_change_password)
                                    <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-700 border border-yellow-200">
                                        Must Change Password
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-700 border border-gray-200">
                                        Password Changed
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin-users.edit', $adminUser) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                Edit Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Account Information
                        </h3>

                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500">Name</dt>
                                <dd class="font-medium text-gray-900 mt-1">
                                    {{ $adminUser->name }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Email</dt>
                                <dd class="font-medium text-gray-900 mt-1">
                                    {{ $adminUser->email }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Username</dt>
                                <dd class="font-medium text-gray-900 mt-1">
                                    {{ $adminUser->username ?: 'N/A' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Role</dt>
                                <dd class="font-medium text-gray-900 mt-1">
                                    {{ $adminUser->role?->name ?? 'No role assigned' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Type</dt>
                                <dd class="font-medium text-gray-900 mt-1">
                                    {{ ucfirst($adminUser->type ?? 'N/A') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Last Login</dt>
                                <dd class="font-medium text-gray-900 mt-1">
                                    {{ $adminUser->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Temporary Password Expires</dt>
                                <dd class="font-medium text-gray-900 mt-1">
                                    {{ $adminUser->temporary_password_expires_at?->format('M d, Y h:i A') ?? 'N/A' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-gray-500">Created At</dt>
                                <dd class="font-medium text-gray-900 mt-1">
                                    {{ $adminUser->created_at?->format('M d, Y h:i A') }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            Password Access
                        </h3>

                        <p class="text-sm text-gray-500 mb-4">
                            Send a new temporary password to this user's email.
                        </p>

                        @php
                            $confirmMessage = $adminUser->must_change_password
                                ? 'Send a new temporary password to this user?'
                                : 'This user has already changed their password. Sending a new temporary password will reset their current password and require them to change it again. Continue?';
                        @endphp

                        <form method="POST"
                              action="{{ route('admin-users.resend-temporary-password', $adminUser) }}"
                              onsubmit="return confirm('{{ $confirmMessage }}');">
                            @csrf

                            <button type="submit"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                                Resend Temporary Password
                            </button>
                        </form>

                        <div class="mt-4 text-xs text-gray-500">
                            This will set <code>must_change_password</code> to true.
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>