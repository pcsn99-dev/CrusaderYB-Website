<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Review Writeup
            </h2>

            <a href="{{ route('writeups.review.index') }}"
               class="text-sm text-gray-600 hover:text-gray-900">
                Back to Queue
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:writeup-review-detail :writeup="$writeup" />
        </div>
    </div>
</x-app-layout>