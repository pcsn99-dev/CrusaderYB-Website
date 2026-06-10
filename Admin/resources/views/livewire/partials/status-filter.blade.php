{{-- Status --}}
<div class="card">
    <div class="card-header d-flex flex-column">
        <h3 class="card-title">Review Status</h3>
        <small class="text-muted">
            Filter writeups by their current review state
        </small>
    </div>

    <div class="card-body p-0">
        <ul class="nav flex-column p-0">
            <li class="nav-item">
                <button type="button"
                        wire:click="setStatus(null)"
                        class="nav-link d-flex justify-content-between align-items-center w-100 text-start">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-list-columns-reverse mr-3"></i>
                        All Writeups
                    </div>
                        
                    <span class="badge rounded-pill text-bg-primary">
                    {{ $statusCounts['all'] ?? 0 }}
                    </span>
                </button>

            </li>

            <li class="nav-item">
                <button type="button"
                        wire:click="setStatus('pending')"
                        class="nav-link d-flex justify-content-between align-items-center w-100 text-start">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-list-columns-reverse mr-3"></i>
                        Pending Review
                    </div>
                        
                    <span class="badge rounded-pill text-bg-primary">
                    {{ $statusCounts['pending'] ?? 0 }}
                    </span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button"
                        wire:click="setStatus('in_review')"
                        class="nav-link d-flex justify-content-between align-items-center w-100 text-start">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-list-columns-reverse mr-3"></i>
                        In Review
                    </div>
                        
                    <span class="badge rounded-pill text-bg-primary">
                    {{ $statusCounts['in_review'] ?? 0 }}
                    </span>
                </button>
            </li>
            <li class="nav-item">
                <button type="button"
                    wire:click="toggleFlaggedOnly"
                    class="nav-link d-flex justify-content-between align-items-center w-100 text-start">

                <div class="d-flex align-items-center gap-2">

                    <i class="bi {{ $flaggedOnly ? 'bi-flag-fill text-danger' : 'bi-flag text-muted' }}"></i>

                    <span>Flagged</span>

                </div>

                <span class="badge rounded-pill text-bg-primary">
                    {{ $statusCounts['flagged_only'] ?? 0 }}
                </span>

            </button>
            </li>
        </ul>
    </div>
</div>