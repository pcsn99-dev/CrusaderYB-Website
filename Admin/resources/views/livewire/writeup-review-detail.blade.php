<div class="space-y-6" wire:poll.5s>
  <x-alert />

    @php
        $student = $writeup->studentInfo;

        $statusClass = match ($writeup->review_status) {
            'reviewed' => 'bg-green-50 text-green-700 border-green-200',
            'in_review' => 'bg-blue-50 text-blue-700 border-blue-200',
            'flagged' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    @endphp
    <div class="row g-3">
        <div class="col-md-3">
            {{-- student card --}}
            <div class="card">
                	<div class="card-body text-center">
                  
						<i class="bi bi-person-circle fs-1 text-primary"></i>

						
						<h3 class="h5 mb-2 fw-bold">{{ $student?->formatted_full_name ?? 'No student info' }}</h3>
						<ul class="list-group list-group-flush text-start small">
							<li class="list-group-item d-flex justify-content-between px-0">
								<span class="text-secondary">University ID</span>
								<span class="fw-semibold"> {{ $student?->university_id ?? 'N/A' }}</span>
							</li>
							<li class="list-group-item d-flex justify-content-between px-0">
								<span class="text-secondary">SLMIS ID </span>
								<span class="fw-semibold">{{ $student?->slmis_id ?? 'N/A' }}</span>
							</li>
							<li class="list-group-item d-flex justify-content-between px-0">
								<span class="text-secondary">Batch</span>
								<span class="fw-semibold">{{ $student?->year ?? 'N/A' }}</span>
							</li>
						</ul>
                  </div>
            </div>

			{{-- academic info card  --}}
            <div class="card mt-3">
				<div class="card-header">
					<h3 class="card-title">Academic Info</h3>
				</div>
				<div class="card-body small">
					<p class="fw-semibold mb-1">
						<i class="bi bi-bank me-1 text-secondary" aria-hidden="true"></i>
						College
					</p>
					<p class="text-secondary mb-3">
						{{ $student?->college?->college_name ?? 'No college' }}
					</p>
					<p class="fw-semibold mb-1">
						<i class="bi bi-mortarboard me-1 text-secondary" aria-hidden="true"></i>
						Program
					</p>
					
						@if ($student?->program)
						<p class="text-secondary mb-3">
						{{ $student->program->program_name }}
						</p>
						@endif
					
					<p class="fw-semibold mb-1">
						<i class="bi bi-journal-bookmark-fill me-1 text-secondary" aria-hidden="true"></i>
						Major
					</p>
					<p class="mb-3">
						@if ($student?->major)
							<span class="badge text-bg-secondary me-1"> {{ $student->major->major_name }}</span>
						@else
							<span class="badge text-bg-secondary me-1"> None</span>
						@endif
					</p>
				
				
				</div>
            </div>

			

        </div>

		<div class="col-md-9">
			{{-- buttons  --}}
			<div class="card  mb-4">
				<div class="card-body text-end">
					@if ($canProofread && $canStartReview)
						<button class="btn btn-primary fw-bold mb-2"
								type="button"
								style="font-size: 0.75rem;"
								wire:click="startReview">
							START REVIEW
						</button>
					@endif

					@if ($canProofread && $canEdit)
                        <button type="button"
                                wire:click="saveChanges"
								style="font-size: 0.75rem;"
                                class="btn btn-success fw-bold mb-2">
                            SAVE CHANGES
                        </button>

                        <button type="button"
                                wire:click="markReviewed"
								style="font-size: 0.75rem;"
                                onclick="return confirm('Mark this writeup as reviewed?')"
                                class="btn btn-warning fw-bold mb-2">
                            MARK REVIEWED
                        </button>

                        <button type="button"
                                wire:click="releaseReview"
								style="font-size: 0.75rem;"
                                onclick="return confirm('Release this writeup so another staff member can review it?')"
                                class="btn btn-secondary fw-bold mb-2">
                            RELEASE REVIEW
                        </button>
                    @endif
						
					@if ($canProofread)
						<button class="btn btn-danger fw-bold mb-2"
								type="button"
								style="font-size: 0.75rem;"
								wire:click="toggleFlag">
							{{ $writeup->is_flagged ? 'REMOVE FLAG' : 'FLAG WRITEUP' }}
						</button>
					@endif
					
				</div>
			</div>

			<div class="row g-3">
				{{-- original  --}}
				<div class="col-md-6">
					<div class="card card-secondary card-outline mb-4">
						<div class="card-header">
							<div class="d-flex justify-content-between align-items-start">
								<div>
									<h3 class="card-title float-none">Original</h3>
									<div class="text-muted small mt-1">
										Original submitted writeup
									</div>
								</div>
							<span class="text-muted small fst-italic">
								{{ mb_strlen($writeup->writeup ?? '') }} chars
							</span>
								
							</div>
						</div>
						<div class="card-body">
							<div class="card" aria-hidden="true">
								<div class="card-body" style="height: 300px;">
					
									@if ($writeup->writeup)
										<h5 class="card-title text-muted">
											{!! $this->renderedOriginalWriteup !!}
										</h5>
									@else
										<h5 class="card-title text-muted">
											No original writeup content available.
										</h5>
									@endif
								</div>
							</div>
						</div>
						

					</div>
				</div>

				{{-- edited --}}
				<div class="col-md-6">
					<div class="card card-secondary card-outline mb-4 " >

						<div class="card-header">
							<div class="d-flex justify-content-between align-items-start">
								<div>
									<h3 class="card-title float-none">Reviewed / Edited Writeup</h3>
									<div class="text-muted small mt-1">
										Maximum of {{ $maxCharacters }} characters
									</div>
								</div>

								<span class="text-muted small fst-italic">
									{{ $this->characterCount }} / {{ $maxCharacters }}
								</span>
							</div>
						</div>

						<div class="card-body" >

							{{-- EDIT MODE --}}
							@if ($canProofread && $canEdit)

								<!-- Formatting Help -->
								<div class="card card-light border mb-3">
									<div class="card-body py-2">
										<div class="d-flex flex-wrap gap-2 small text-muted align-items-center">
											<span class="fw-semibold text-secondary">Formatting:</span>

											<code class="bg-white border rounded px-2 py-1">**bold**</code>
											<code class="bg-white border rounded px-2 py-1">*italic*</code>
										</div>	
									</div>
								</div>

								<!-- Textarea -->
								<div class="mb-3">
									<textarea
										wire:model.live.debounce.300ms="editedWriteup"
										maxlength="{{ $maxCharacters }}"
										rows="10"
										class="form-control form-control-sm"
										placeholder="Edit the student's writeup here..."></textarea>

									@error('editedWriteup')
										<div class="text-danger small mt-1">{{ $message }}</div>
									@enderror
								</div>

								<!-- Preview -->
								<div class="card card-light border">
									<div class="card-header py-2 d-flex justify-content-between align-items-center">
										<span class="small text-uppercase text-muted fw-semibold">
											Preview
										</span>

										<span class="small {{ $this->remainingCharacters <= 20 ? 'text-danger fw-semibold' : 'text-muted' }}">
											{{ $this->remainingCharacters }} remaining
										</span>
									</div>

									<div class="card-body">
										@if ($editedWriteup)
											<div class="small text-body">
												{!! $this->renderedEditedWriteup !!}
											</div>
										@else
											<p class="text-muted small mb-0">
												Preview will appear here while you type.
											</p>
										@endif
									</div>
								</div>

							{{-- READ ONLY MODE --}}
							@else

								<div class="card card-light border">
									<div class="card-body" style="min-height: 280px;">

										@if ($writeup->edited_writeup || $writeup->writeup)
											<div class="text-body small">
												{!! \Illuminate\Support\Str::markdown(
													$writeup->edited_writeup ?: $writeup->writeup,
													[
														'html_input' => 'strip',
														'allow_unsafe_links' => false,
													]
												) !!}
											</div>
										@else
											<p class="text-muted small mb-0">
												No writeup content available.
											</p>
										@endif

									</div>
								</div>

								<p class="text-muted small mt-2">
									@if (! $canProofread)
										You only have permission to view writeups.
									@else
										Start reviewing this writeup to edit the reviewed version.
									@endif
								</p>

							@endif

						</div>
					</div>
				</div>
			</div>
				
		</div>
    </div>
</div>