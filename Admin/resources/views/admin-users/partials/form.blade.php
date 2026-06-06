<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
                Full Name
            </label>

            <input type="text"
                   name="name"
                   id="name"
                   value="{{ old('name', $adminUser?->name) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="Example: Juan Dela Cruz">

            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                Email Address
            </label>

            <input type="email"
                   name="email"
                   id="email"
                   value="{{ old('email', $adminUser?->email) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="staff@example.com">

            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">
                Username
            </label>

            <input type="text"
                   name="username"
                   id="username"
                   value="{{ old('username', $adminUser?->username) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="Example: jdelacruz">

            @error('username')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="role_id" class="block text-sm font-medium text-gray-700">
                Role
            </label>

            <select name="role_id"
                    id="role_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select Role</option>

                @foreach ($roles as $role)
                    <option value="{{ $role->id }}"
                            @selected((int) old('role_id', $adminUser?->role_id) === (int) $role->id)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

            @error('role_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 p-4">
        <label class="flex items-start gap-3">
            <input type="checkbox"
                   name="active"
                   value="1"
                   class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                   @checked(old('active', $adminUser?->active ?? true))>

            <div>
                <div class="text-sm font-medium text-gray-900">
                    Active Account
                </div>
                <p class="text-sm text-gray-500">
                    Active admin users can log in. Inactive users will be blocked after we add the login restriction middleware.
                </p>
            </div>
        </label>

        @error('active')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if (! $adminUser)
        <div class="rounded-md bg-yellow-50 p-4 text-sm text-yellow-700 border border-yellow-200">
            A temporary password will be generated and sent to this user's email. The user will be required to change it after logging in.
        </div>
    @endif

    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
        <a href="{{ route('admin-users.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
            Cancel
        </a>

        <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            {{ $buttonText }}
        </button>
    </div>
</div>