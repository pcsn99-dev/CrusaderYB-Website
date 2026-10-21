<x-app-layout>
    <x-slot name="header">
        Penalties
    </x-slot>

    <x-slot name="subheader">
        View and manage student penalties.
    </x-slot>

    <x-slot name="headerIcon">
        <i class="bi bi-exclamation-diamond"></i>
    </x-slot>

    <x-slot name="breadcrumbs">
        <li class="breadcrumb-item active">
            Penalties
        </li>
    </x-slot>

    <div class="cyb-page">
        <div
            id="penalty-management-app"
            data-years='@json($years)'
            data-can-manage="{{ $canManage ? 'true' : 'false' }}"
        ></div>

        @push('scripts')
            @vite('resources/js/penalties.ts')
        @endpush
    </div>
</x-app-layout>