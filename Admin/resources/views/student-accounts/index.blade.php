<x-app-layout>
    <x-slot name="header">
        Student Accounts
    </x-slot>

    <x-slot name="subheader">
        Search and review CYB student account information.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-person-vcard"></i>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item active">
            Student Accounts
        </li>
    </x-slot>

    <div class="cyb-page">
        <div
            id="student-accounts-app"
            data-colleges='@json($colleges)'
            data-graduation-years='@json($graduationYears)'
        ></div>
    </div>

    @vite('resources/js/student-accounts.ts')
</x-app-layout>