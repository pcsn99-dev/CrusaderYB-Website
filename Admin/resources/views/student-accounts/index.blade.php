<x-app-layout>
    <div
        id="student-accounts-app"
        data-colleges='@json($colleges)'
        data-graduation-years='@json($graduationYears)'
    ></div>

    @vite('resources/js/student-accounts.ts')
</x-app-layout>