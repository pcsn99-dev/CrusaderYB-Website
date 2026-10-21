<x-app-layout>
    <x-slot name="header">
        Pictorial Attendance
    </x-slot>

    <x-slot name="subheader">
        View and manage student attendance records.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-calendar-check"></i>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item active">
            Attendance
        </li>
    </x-slot>

    <div class="cyb-page">
        <div
            id="pictorial-attendance-app"
            data-active-year='@json($activeYear)'
            data-can-manage="{{ $canManage ? 'true' : 'false' }}"
        ></div>

        @push('scripts')
            @vite('resources/js/pictorial-attendance.ts')
        @endpush
    </div>
</x-app-layout>