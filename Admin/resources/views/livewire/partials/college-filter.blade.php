<div class="card mt-3">
    <div class="card-header d-flex flex-column">
        <h3 class="card-title">Colleges/Schools</h3>
        <small class="text-muted">Filter writeups by college/schools</small>
    </div>
    
    <div class="card-body p-0 d-flex flex-column" style="max-height: 400px; overflow-y: auto; min-height: 0;">

        <ul class="nav navpills flex-column mb-0" >
            <li class="nav-item">
                <button type="button"
                        wire:click="setCollege(null)" 
                        class="nav-link d-flex justify-content-between align-items-center w-100"
                        href="#">
                    All
                    <span class="badge rounded-pill text-bg-primary">
                    {{ $totalCount }}
                    </span>
                </button>
            </li>


            @foreach($colleges as $college)
                <li class="nav-item w-100">
                    <button type="button"
                            wire:click="setCollege({{ $college->id }})"
                            class="nav-link d-flex align-items-center w-100 text-start">

                        <span class="flex-grow-1 text-truncate" style="min-width:0;">
                            {{ $college->college_name }}
                        </span>

                        <span class="badge rounded-pill text-bg-primary ms-2 flex-shrink-0">
                            {{ $collegeCounts[$college->id] ?? 0 }}
                        </span>

                    </button>
                </li>
            @endforeach
        </ul>


    </div>

</div>