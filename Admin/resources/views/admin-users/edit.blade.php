<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Admin User
            </h2>

            <a href="{{ route('admin-users.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                Back to Admin Users
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $adminUser->name }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            Update this admin account's details, role, and account status.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('admin-users.update', $adminUser) }}">
                        @csrf
                        @method('PUT')

                        @include('admin-users.partials.form', [
                            'adminUser' => $adminUser,
                            'roles' => $roles,
                            'buttonText' => 'Update Admin User',
                        ])
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>