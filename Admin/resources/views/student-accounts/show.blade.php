<x-app-layout>
    <x-slot name="header">
        Student Account
    </x-slot>

    <x-slot name="subheader">
        View student information, yearbook status, reservations, and penalties.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-person-vcard"></i>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item">
            <a href="{{ route('student-accounts.index') }}">
                Student Accounts
            </a>
        </li>

        <li class="breadcrumb-item active">
            Student Details
        </li>
    </x-slot>

    <div class="cyb-page">
        <div
            id="student-account-show-app"
            data-student='@json($student)'
        ></div>

        @push('scripts')
            @vite('resources/js/student-account-show.ts')
        @endpush
    </div>
</x-app-layout>