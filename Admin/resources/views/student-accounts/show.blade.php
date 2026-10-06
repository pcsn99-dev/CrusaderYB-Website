<x-app-layout>
    <div
        id="student-account-show-app"
        data-student='@json($student)'
    ></div>

    @vite('resources/js/student-account-show.ts')
</x-app-layout>