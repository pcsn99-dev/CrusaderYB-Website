<div wire:poll.5s>

    <div class="row g-3">
        
        <div class="col-lg-3">

            <button type="button"
                    wire:click="clearFilters" 
                    class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2 mb-3">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
                Clear Filters
            </button>

            {{-- Status Filter --}}
            @include('livewire.partials.status-filter')

            <!-- College/Schools -->
            @include('livewire.partials.college-filter')
            

        </div>

        <!-- Inbox List -->
       <div class="col-lg-9">
            <div class="card">

                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">

                    <!-- Title -->
                    <h3 class="card-title m-0">
                        WriteUp List
                    </h3>

                    <!-- Tools -->
                    <div class="card-tools d-flex ms-auto">

                        <div class="input-group input-group-sm" style="width: 260px; max-width: 100%;">
                            <button type="submit" class="input-group-text">
                                <i class="bi bi-search"></i>
                            </button>

                            <input id="search"
                                type="text"
                                wire:model.live.debounce.500ms="search"
                                class="form-control"
                                placeholder="Search student name, ID, or SLMIS ID">
                        </div>

                    </div>

                </div>

                <div class="card-body p-0">
                    <div class="d-flex align-items-center px-3 py-2 border-bottom">
                        
                        <div class="btn-group btn-group-sm ms-0">
                            <button class="btn btn-outline-secondary"
                                    type="button"
                                    title="Refresh"
                                    wire:click="$refresh">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" title="Archive">
                            <i class="bi bi-archive" aria-hidden="true"></i>
                            </button>
                            <button class="btn btn-outline-secondary" type="button" title="Mark as spam">
                            <i class="bi bi-exclamation-octagon" aria-hidden="true"></i>
                            </button>
    
                        </div>
                        <span class="ms-auto text-secondary small">
                            {{ $writeups->firstItem() }}–{{ $writeups->lastItem() }} of {{ $writeups->total() }}
                        </span>
                    </div>

                    <ul class="list-group list-group-flush list-group-hover mb-0">
                        @forelse ($writeups as $writeup)

                            @php
                                $student = $writeup->studentInfo;

                                $preview = \Illuminate\Support\Str::limit(
                                    trim(strip_tags($writeup->edited_writeup ?: $writeup->writeup)),
                                    120
                                );

                                $statusClass = match ($writeup->review_status) {
                                    'reviewed' => 'text-bg-success',
                                    'in_review' => 'text-bg-info',
                                    'flagged' => 'bg-warning text-dark border border-warning',
                                    default => 'text-bg-secondary',
                                };
                            @endphp

                            <li wire:key="writeup-row-{{ $writeup->id }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 fw-semibold">
                                

                                @if ($canProofread)
                                    <button type="button"
                                            wire:key="flag-button-{{ $writeup->id }}"
                                            wire:click="toggleFlag({{ $writeup->id }})"
                                            title="{{ $writeup->is_flagged ? 'Remove flag' : 'Flag writeup' }}"
                                            class="text-lg leading-none {{ $writeup->is_flagged ? 'text-yellow-500' : 'text-gray-300 hover:text-yellow-500' }} mr-2">
                                        
                                            @if ($writeup->is_flagged)
                                                <i class="bi bi-flag-fill text-danger"></i>
                                            @else
                                                <i class="bi bi-flag"></i>
                                            @endif
                                    </button>
                                @else
                                    <span class="text-gray-300 text-lg">
                                        @if ($writeup->is_flagged)
                                            <i class="bi bi-flag-fill text-danger"></i>
                                        @else
                                            <i class="bi bi-flag"></i>
                                        @endif
                                    </span>
                                @endif

                                <a href="{{ route('writeups.review.show', $writeup) }}"
                                class="d-grid gap-1 text-decoration-none text-body w-100 align-items-start"
                                style="grid-template-columns: 10rem 1fr auto auto; min-width: 0;">

                                    {{-- NAME + BATCH --}}
                                    <span class="d-flex flex-column min-w-0">
                                        <span class="text-truncate fw-semibold">
                                            {{ $student?->formatted_full_name ?? 'No student info' }}
                                        </span>

                                        @if ($student?->year)
                                            <span class="small text-muted fw-normal">
                                                Batch {{ $student->year }}
                                            </span>
                                        @endif
                                    </span>

                                    {{-- COLLEGE + COURSE + PREVIEW --}}
                                    <span class="d-flex flex-column min-w-0">
                                        <span class="text-truncate">
                                            <span class="fw-semibold">
                                                {{ $student?->college?->college_name ?? 'No college' }}
                                            </span>

                                            @if ($student?->program)
                                                <span class="text-muted"> — </span>
                                                <span>
                                                    {{ $student->program->program_name }}
                                                </span>
                                            @endif
                                        </span>

                                        <span class="small text-muted text-truncate fw-normal">
                                            {{ $preview ?: 'No writeup content available.' }}
                                        </span>

                                        <span>
                                            @if ($writeup->lockedBy)
                                                <div class="mt-1 text-xs fw-normal text-blue-700 truncate">
                                                    Being reviewed by {{ $writeup->lockedBy->name }}
                                                    @if ($writeup->locked_at)
                                                        · {{ $writeup->locked_at->diffForHumans() }}
                                                    @endif
                                                </div>
                                            @endif
                                        </span>

                                    </span>
                                   
                                    {{-- STATUS --}}
                                    <span class="d-flex flex-column align-items-end gap-1">

                                        <span class="badge rounded-pill {{ $statusClass }} ">
                                            {{ ucfirst(str_replace('_', ' ', $writeup->review_status)) }}
                                        </span>

                                        @if ($student?->program?->is_graduate_program)
                                            <span class="badge bg-info text-dark">
                                                Graduate
                                            </span>
                                        @endif

                                    </span>

                                    

                                    

                                </a>

                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">
                                No writeups found.
                            </li>
                        @endforelse
                    </ul>
                    <!-- pagination -->
                    <div class="px-4 py-4 border-t border-gray-200">
                        {{ $writeups->links() }}
                    </div>
                
            
                </div>

            </div>



        </div>


				

	    </div>
</div>

