<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        You are using a temporary password. Please create a new password before continuing to the admin portal.
    </div>

    @if ($temporaryPasswordExpired)
        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700 border border-red-200">
            Your temporary password has expired. Please ask a Super Admin to send you a new temporary password.
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700 border border-red-200">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="logout-form" method="POST" action="{{ route('logout') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('password.force.update') }}">
        @csrf
        @method('PATCH')

        <div>
            <x-input-label for="current_password" value="Temporary Password" />

            <x-text-input id="current_password"
                          class="block mt-1 w-full"
                          type="password"
                          name="current_password"
                          required
                          :disabled="$temporaryPasswordExpired"
                          autocomplete="current-password" />

            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="New Password" />

            <x-text-input id="password"
                          class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required
                          :disabled="$temporaryPasswordExpired"
                          autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirm New Password" />

            <x-text-input id="password_confirmation"
                          class="block mt-1 w-full"
                          type="password"
                          name="password_confirmation"
                          required
                          :disabled="$temporaryPasswordExpired"
                          autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6 flex items-center justify-between">
            <button type="submit"
                    form="logout-form"
                    class="text-sm text-gray-600 hover:text-gray-900 underline">
                Log out
            </button>

            <x-primary-button :disabled="$temporaryPasswordExpired">
                Change Password
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>