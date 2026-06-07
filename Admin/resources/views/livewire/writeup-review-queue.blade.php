<div class="space-y-6">

    <!-- Header / Search Bar -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        Writeup Review Queue
                    </h3>

                    <p class="text-sm text-gray-500">
                        Review submitted yearbook writeups by status, college, and student information.
                    </p>
                </div>

                <div class="w-full lg:w-96">
                    <label for="search" class="sr-only">Search writeups</label>

                    <input type="text"
                           id="search"
                           wire:model.live.debounce.500ms="search"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Search student name, ID, or SLMIS ID">
                </div>
            </div>
        </div>
    </div>

    <!-- Main Mailbox Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Sidebar Filters -->
        <aside class="lg:col-span-3 space-y-4">

            <!-- Year Filter -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <label for="year" class="block text-sm font-semibold text-gray-900">
                        Year / Batch
                    </label>

                    <select id="year"
                            wire:model.live="year"
                            class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All years</option>

                        @foreach ($years as $availableYear)
                            <option value="{{ $availableYear }}">
                                {{ $availableYear }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status Folders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-900">
                        Review Status
                    </h4>
                </div>

                <div class="divide-y divide-gray-100">

                    <!-- add new status filters here if more review states are added -->
                    <button type="button"
                            wire:click="setStatus(null)"
                            class="w-full flex items-center justify-between px-4 py-3 text-sm hover:bg-gray-50 {{ $status === null && ! $flaggedOnly ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700' }}">
                        <span>All Writeups</span>
                        <span class="text-xs rounded-full bg-gray-100 px-2 py-0.5">
                            {{ $statusCounts['all'] ?? 0 }}
                        </span>
                    </button>

                    <button type="button"
                            wire:click="setStatus('pending')"
                            class="w-full flex items-center justify-between px-4 py-3 text-sm hover:bg-gray-50 {{ $status === 'pending' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700' }}">
                        <span>Pending Review</span>
                        <span class="text-xs rounded-full bg-gray-100 px-2 py-0.5">
                            {{ $statusCounts['pending'] ?? 0 }}
                        </span>
                    </button>

                    <button type="button"
                            wire:click="setStatus('in_review')"
                            class="w-full flex items-center justify-between px-4 py-3 text-sm hover:bg-gray-50 {{ $status === 'in_review' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700' }}">
                        <span>In Review</span>
                        <span class="text-xs rounded-full bg-gray-100 px-2 py-0.5">
                            {{ $statusCounts['in_review'] ?? 0 }}
                        </span>
                    </button>

                    <button type="button"
                            wire:click="setStatus('reviewed')"
                            class="w-full flex items-center justify-between px-4 py-3 text-sm hover:bg-gray-50 {{ $status === 'reviewed' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700' }}">
                        <span>Reviewed</span>
                        <span class="text-xs rounded-full bg-gray-100 px-2 py-0.5">
                            {{ $statusCounts['reviewed'] ?? 0 }}
                        </span>
                    </button>

                    <button type="button"
                            wire:click="toggleFlaggedOnly"
                            class="w-full flex items-center justify-between px-4 py-3 text-sm hover:bg-gray-50 {{ $flaggedOnly ? 'bg-yellow-50 text-yellow-700 font-semibold' : 'text-gray-700' }}">
                        <span>Flagged</span>
                        <span class="text-xs rounded-full bg-gray-100 px-2 py-0.5">
                            {{ $statusCounts['flagged_only'] ?? 0 }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Colleges -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-900">
                        Colleges
                    </h4>
                </div>

                <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                    <button type="button"
                            wire:click="setCollege(null)"
                            class="w-full flex items-center justify-between px-4 py-3 text-sm hover:bg-gray-50 {{ $collegeId === null ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700' }}">
                        <span>All Colleges</span>
                        <span class="text-xs rounded-full bg-gray-100 px-2 py-0.5">
                            {{ $totalCount }}
                        </span>
                    </button>

                    @foreach ($colleges as $college)
                        <button type="button"
                                wire:click="setCollege({{ $college->id }})"
                                class="w-full flex items-center justify-between gap-3 px-4 py-3 text-left text-sm hover:bg-gray-50 {{ (int) $collegeId === (int) $college->id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700' }}">
                            <span class="truncate">
                                {{ $college->college_name }}
                            </span>

                            <span class="shrink-0 text-xs rounded-full bg-gray-100 px-2 py-0.5">
                                {{ $collegeCounts[$college->id] ?? 0 }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Clear Filters -->
            <button type="button"
                    wire:click="clearFilters"
                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                Clear Filters
            </button>
        </aside>

        <!-- Inbox List -->
        <main class="lg:col-span-9">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <!-- Inbox Toolbar -->
                <div class="px-4 py-3 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">
                                Inbox
                            </h4>

                            <p class="text-xs text-gray-500">
                                Showing {{ $writeups->firstItem() ?? 0 }}–{{ $writeups->lastItem() ?? 0 }} of {{ $writeups->total() }} writeups
                            </p>
                        </div>

                        <div class="text-xs text-gray-500">
                            Filters update automatically
                        </div>
                    </div>
                </div>

                <!-- Writeup Rows -->
                <div class="divide-y divide-gray-200">
                    @forelse ($writeups as $writeup)
                        @php
                            $student = $writeup->studentInfo;

                            $preview = \Illuminate\Support\Str::limit(
                                trim(strip_tags($writeup->edited_writeup ?: $writeup->writeup)),
                                120
                            );

                            $statusClass = match ($writeup->review_status) {
                                'reviewed' => 'bg-green-50 text-green-700 border-green-200',
                                'in_review' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'flagged' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                default => 'bg-gray-50 text-gray-700 border-gray-200',
                            };
                        @endphp

                        <div class="group px-4 py-3 hover:bg-gray-50 transition">
                            <div class="grid grid-cols-1 xl:grid-cols-12 gap-3 xl:gap-4 items-start xl:items-center">

                                <!-- Flag Button -->
                                <div class="xl:col-span-1 flex xl:justify-center">
                                    <button type="button"
                                            wire:click="toggleFlag({{ $writeup->id }})"
                                            title="{{ $writeup->is_flagged ? 'Remove flag' : 'Flag writeup' }}"
                                            class="text-lg leading-none {{ $writeup->is_flagged ? 'text-yellow-500' : 'text-gray-300 hover:text-yellow-500' }}">
                                        {{ $writeup->is_flagged ? '★' : '☆' }}
                                    </button>
                                </div>

                                <!-- Student / Sender Column -->
                                <div class="xl:col-span-3 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="font-semibold text-sm text-gray-900 truncate">
                                            {{ $student?->formatted_full_name ?? 'No student info' }}
                                        </div>

                                        @if ($writeup->locked_by)
                                            <span class="hidden sm:inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700 border border-blue-200">
                                                Locked
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-0.5 text-xs text-gray-500 truncate">
                                        {{ $student?->university_id ?? 'No ID' }}
                                        @if ($student?->year)
                                            · Batch {{ $student->year }}
                                        @endif
                                    </div>
                                </div>

                                <!-- Subject / Preview Column -->
                                <div class="xl:col-span-5 min-w-0">
                                    <div class="text-sm text-gray-900 truncate">
                                        <span class="font-semibold">
                                            {{ $student?->college?->college_name ?? 'No college' }}
                                        </span>

                                        @if ($student?->program)
                                            <span class="text-gray-400"> — </span>
                                            <span>
                                                {{ $student->program->program_name }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-0.5 text-sm text-gray-500 truncate">
                                        {{ $preview ?: 'No writeup content available.' }}
                                    </div>

                                    @if ($writeup->lockedBy)
                                        <div class="mt-1 text-xs text-blue-700 truncate">
                                            Being reviewed by {{ $writeup->lockedBy->name }}
                                            @if ($writeup->locked_at)
                                                · {{ $writeup->locked_at->diffForHumans() }}
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- Status Column -->
                                <div class="xl:col-span-2 flex flex-wrap gap-2 xl:justify-end">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium border {{ $statusClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $writeup->review_status)) }}
                                    </span>

                                    @if ($student?->program?->is_graduate_program)
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 border border-indigo-200">
                                            Graduate
                                        </span>
                                    @endif
                                </div>

                                <!-- Date / Action Column -->
                                <div class="xl:col-span-1 flex xl:flex-col items-center xl:items-end justify-between gap-2">
                                    <div class="text-xs text-gray-500 whitespace-nowrap">
                                        {{ $writeup->updated_at?->format('M d') ?? 'N/A' }}
                                    </div>

                                    {{-- add view route later when detail page exists --}}
                                    @if (Route::has('writeups.review.show'))
                                        <a href="{{ route('writeups.review.show', $writeup) }}"
                                        class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">
                                            View
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-12 text-center">
                            <div class="text-sm font-medium text-gray-700">
                                No writeups found.
                            </div>

                            <p class="mt-1 text-sm text-gray-500">
                                Try changing the selected filters or search keyword.
                            </p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="px-4 py-4 border-t border-gray-200">
                    {{ $writeups->links() }}
                </div>
            </div>
        </main>
    </div>
</div>