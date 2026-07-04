<?php

namespace App\Livewire;

use App\Models\College;
use App\Models\StudentInfo;
use App\Models\Writeup;
use Livewire\Component;
use Livewire\WithPagination;

class WriteupReviewQueue extends Component
{
    use WithPagination;

    //permissions
    private function canProofreadWriteups(): bool
    {
        return auth()->user()?->hasPermission('proofread-writeups') ?? false;
    }

    private function blockIfCannotProofread(): bool
    {
        if (! $this->canProofreadWriteups()) {
            session()->flash('error', 'You do not have permission to perform this action.');
            return true;
        }

        return false;
    }



    public ?string $year = null;
    public ?int $collegeId = null;
    public ?string $status = null;
    public bool $flaggedOnly = false;
    public string $search = '';
    public int $perPage = 12;

    protected $queryString = [
        'year' => ['except' => ''],
        'collegeId' => ['except' => ''],
        'status' => ['except' => ''],
        'flaggedOnly' => ['except' => false],
        'search' => ['except' => ''],
    ];

    public function updatingYear(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setStatus(?string $status = null): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function setCollege(?int $collegeId = null): void
    {
        $this->collegeId = $collegeId;
        $this->resetPage();
    }

    public function toggleFlaggedOnly(): void
    {
        $this->flaggedOnly = ! $this->flaggedOnly;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'year',
            'collegeId',
            'status',
            'flaggedOnly',
            'search',
        ]);

        $this->resetPage();
    }



    public function render()
    {
        $writeups = Writeup::forReviewQueue()
            ->filterByYear($this->year)
            ->filterByCollege($this->collegeId)
            ->filterByStatus($this->status)
        ->flaggedOnly($this->flaggedOnly)
            ->searchStudent($this->search)
            ->orderedForReviewQueue()
            ->paginate($this->perPage);

        return view('livewire.writeup-review-queue', [
            'writeups' => $writeups,
            'years' => $this->getYears(),
            'colleges' => $this->getColleges(),
            'statuses' => $this->getStatuses(),
            'statusCounts' => $this->getStatusCounts(),
            'collegeCounts' => $this->getCollegeCounts(),
            'totalCount' => $this->getBaseQuery()->count(),
            'canProofread' => $this->canProofreadWriteups(),
        ]);
    }

    private function getBaseQuery()
    {
        return Writeup::query()
            ->whereHas('studentInfo', function ($query) {
                $query->when($this->year, function ($query) {
                    $query->where('year', $this->year);
                });

                $query->when($this->search, function ($query) {
                    $query->where(function ($query) {
                        $query->where('first_name', 'like', "%{$this->search}%")
                            ->orWhere('middle_name', 'like', "%{$this->search}%")
                            ->orWhere('last_name', 'like', "%{$this->search}%")
                            ->orWhere('university_id', 'like', "%{$this->search}%")
                            ->orWhere('slmis_id', 'like', "%{$this->search}%");
                    });
                });
            });
    }

    private function getYears()
    {
        return StudentInfo::query()
            ->whereNotNull('year')
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }

    private function getColleges()
    {
        return College::query()
            ->orderBy('college_name')
            ->get();
    }

    private function getStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'in_review' => 'In Review',
            'reviewed' => 'Reviewed',
            'flagged' => 'Flagged',
        ];
    }

    private function getStatusCounts(): array
    {
        $counts = [];

        foreach (array_keys($this->getStatuses()) as $status) {
            $counts[$status] = (clone $this->getBaseQuery())
                ->where('review_status', $status)
                ->count();
        }

        $counts['all'] = $this->getBaseQuery()->count();

        $counts['flagged_only'] = (clone $this->getBaseQuery())
            ->where('is_flagged', true)
            ->count();

        return $counts;
    }

    private function getCollegeCounts(): array
    {
        $counts = [];

        $colleges = College::query()->pluck('id');

        foreach ($colleges as $collegeId) {
            $counts[$collegeId] = (clone $this->getBaseQuery())
                ->whereHas('studentInfo', function ($query) use ($collegeId) {
                    $query->where('college_id', $collegeId);
                })
                ->count();
        }

        return $counts;
    }



    //flagging
    public function toggleFlag(int $writeupId): void
    {

        if ($this->blockIfCannotProofread()) { return;}
        $writeup = Writeup::findOrFail($writeupId);

        if ($writeup->is_flagged) {
            $writeup->update([
                'is_flagged' => false,
                'flag_reason' => null,
                'flagged_by' => null,
                'flagged_at' => null,
            ]);
            return;
        }

        $writeup->update([
            'is_flagged' => true,
            'flagged_by' => auth()->id(),
            'flagged_at' => now(),
        ]);
    }

    public function startReview(int $writeupId)
    {
        if ($this->blockIfCannotProofread()) { return;}
        $writeup = Writeup::findOrFail($writeupId);

        if ($writeup->locked_by && ! $writeup->lockExpired() && (int) $writeup->locked_by !== (int) auth()->id()) {
            session()->flash('error', 'This writeup is already being reviewed by another staff member.');
            return;
        }

        $writeup->update([
            'locked_by' => auth()->id(),
            'locked_at' => now(),
            'review_status' => 'in_review',
        ]);

        return redirect()->route('writeups.review.show', $writeup);
    }

    public function releaseReview(int $writeupId): void
    {
        if ($this->blockIfCannotProofread()) { return;}
        $writeup = Writeup::findOrFail($writeupId);

        if ((int) $writeup->locked_by !== (int) auth()->id()) {
            session()->flash('error', 'You can only release writeups that you are currently reviewing.');
            return;
        }

        $writeup->update([
            'locked_by' => null,
            'locked_at' => null,
            'review_status' => $writeup->is_flagged ? 'flagged' : 'pending',
        ]);

        session()->flash('success', 'Writeup review released.');
    }


}