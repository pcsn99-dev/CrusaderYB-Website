<div class="space-y-6">

    {{-- Section intro --}}
    <div>
        <h2 class="mb-1 text-base font-semibold text-[var(--cyb-primary)]">
            Account Details
        </h2>

        <p class="mb-0 text-sm text-[var(--cyb-muted)]">
            Fill in the staff member’s information and choose the role that controls their system access.
        </p>
    </div>

    {{-- Name and Email --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold text-[var(--cyb-text)]">
                Full Name
            </label>

            <input type="text"
                   name="name"
                   id="name"
                   value="{{ old('name', $adminUser?->name) }}"
                   required
                   autocomplete="name"
                   placeholder="Example: Juan Dela Cruz"
                   class="@error('name') border-red-300 focus:border-red-500 focus:ring-red-200 @else border-[var(--cyb-border)] focus:border-[var(--cyb-primary)] focus:ring-[var(--cyb-primary)]/20 @enderror block w-full rounded-lg px-3 py-2 text-sm shadow-sm">

            @error('name')
                <p class="mt-2 flex items-center gap-1 text-sm text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-[var(--cyb-text)]">
                Email Address
            </label>

            <input type="email"
                   name="email"
                   id="email"
                   value="{{ old('email', $adminUser?->email) }}"
                   required
                   autocomplete="email"
                   placeholder="staff@example.com"
                   class="@error('email') border-red-300 focus:border-red-500 focus:ring-red-200 @else border-[var(--cyb-border)] focus:border-[var(--cyb-primary)] focus:ring-[var(--cyb-primary)]/20 @enderror block w-full rounded-lg px-3 py-2 text-sm shadow-sm">

            @error('email')
                <p class="mt-2 flex items-center gap-1 text-sm text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    {{-- Username and Role --}}
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
            <label for="username" class="mb-1 block text-sm font-semibold text-[var(--cyb-text)]">
                Username
            </label>

            <input type="text"
                   name="username"
                   id="username"
                   value="{{ old('username', $adminUser?->username) }}"
                   required
                   autocomplete="username"
                   placeholder="Example: jdelacruz"
                   class="@error('username') border-red-300 focus:border-red-500 focus:ring-red-200 @else border-[var(--cyb-border)] focus:border-[var(--cyb-primary)] focus:ring-[var(--cyb-primary)]/20 @enderror block w-full rounded-lg px-3 py-2 text-sm shadow-sm">

            @error('username')
                <p class="mt-2 flex items-center gap-1 text-sm text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label for="role_id" class="mb-1 block text-sm font-semibold text-[var(--cyb-text)]">
                Role
            </label>

            <select name="role_id"
                    id="role_id"
                    required
                    class="@error('role_id') border-red-300 focus:border-red-500 focus:ring-red-200 @else border-[var(--cyb-border)] focus:border-[var(--cyb-primary)] focus:ring-[var(--cyb-primary)]/20 @enderror block w-full rounded-lg px-3 py-2 text-sm shadow-sm">
                <option value="">Select Role</option>

                @foreach ($roles as $role)
                    <option value="{{ $role->id }}"
                            @selected((int) old('role_id', $adminUser?->role_id) === (int) $role->id)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

            @error('role_id')
                <p class="mt-2 flex items-center gap-1 text-sm text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    {{-- Account Status --}}
    <div class="rounded-xl border border-[var(--cyb-border)] bg-[var(--cyb-primary-soft)]/50 p-4">
        <input type="hidden" name="active" value="0">

        <label class="flex cursor-pointer items-start gap-3">
            <input type="checkbox"
                   name="active"
                   value="1"
                   class="mt-1 rounded border-[var(--cyb-border)] text-[var(--cyb-primary)] shadow-sm focus:ring-[var(--cyb-primary)]/30"
                   @checked((bool) old('active', $adminUser?->active ?? true))>

            <div>
                <div class="text-sm font-semibold text-[var(--cyb-text)]">
                    Active Account
                </div>

                <p class="mb-0 text-sm text-[var(--cyb-muted)]">
                    Active admin users can log in. Inactive users will be blocked from accessing the admin panel.
                </p>
            </div>
        </label>

        @error('active')
            <p class="mt-2 flex items-center gap-1 text-sm text-red-600">
                <i class="bi bi-exclamation-circle"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Temporary Password Notice --}}
    @if (! $adminUser)
        <div class="flex items-start gap-3 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            <i class="bi bi-key-fill mt-0.5"></i>

            <div>
                <p class="mb-1 font-semibold">
                    Temporary password required
                </p>

                <p class="mb-0">
                    The system will generate a temporary password and send it to this user’s email. The user must change it after logging in.
                </p>
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex flex-col-reverse gap-3 border-t border-[var(--cyb-border)] pt-5 sm:flex-row sm:items-center sm:justify-end">
        <a href="{{ route('admin-users.index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-[var(--cyb-border)] bg-white px-4 py-2 text-sm font-semibold text-[var(--cyb-text)] shadow-sm transition hover:bg-[var(--cyb-primary-soft)]">
            Cancel
        </a>

        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--cyb-primary)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--cyb-primary-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--cyb-primary)] focus:ring-offset-2">
            <i class="bi bi-check2-circle"></i>
            {{ $buttonText }}
        </button>
    </div>
</div>