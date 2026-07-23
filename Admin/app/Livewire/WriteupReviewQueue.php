<?php

namespace App\Livewire;

use App\Models\College;
use App\Models\StudentInfo;
use App\Models\Writeup;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use App\Support\AuditLogger;

class WriteupReviewQueue extends Component
{
    use WithPagination;

    public ?string $year = null;
    public ?int $collegeId = null;
    public ?string $status = null;
    public bool $flaggedOnly = false;
    public string $search = '';
    public string $contentFilter = 'all';
    public int $perPage = 12;
    public bool $contentFiltersOpen = false;
    public bool $statusFiltersOpen = false;
    public bool $collegeFiltersOpen = false;

    private const VALID_CONTENT_FILTERS = [
        'all',
        'over_300',
        'has_emoji',
        'has_profanity',
    ];

    public function toggleFilterCard(string $card): void
    {
        match ($card) {
            'content' => $this->contentFiltersOpen = ! $this->contentFiltersOpen,
            'status' => $this->statusFiltersOpen = ! $this->statusFiltersOpen,
            'college' => $this->collegeFiltersOpen = ! $this->collegeFiltersOpen,
            default => null,
        };
    }

    protected $queryString = [
        'year' => ['except' => null],
        'collegeId' => ['except' => null],
        'status' => ['except' => null],
        'flaggedOnly' => ['except' => false],
        'search' => ['except' => ''],
        'contentFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        if (! in_array($this->contentFilter, self::VALID_CONTENT_FILTERS, true)) {
            $this->contentFilter = 'all';
        }
    }

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

    public function updatingYear(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingContentFilter(): void
    {
        $this->resetPage();
    }

    public function setStatus(?string $status = null): void
    {
        $this->status = $status;
        $this->flaggedOnly = false;
        $this->resetPage();
    }

    public function setCollege(?int $collegeId = null): void
    {
        $this->collegeId = $collegeId;
        $this->resetPage();
    }

    public function setContentFilter(string $filter = 'all'): void
    {
        if (! in_array($filter, self::VALID_CONTENT_FILTERS, true)) {
            $filter = 'all';
        }

        $this->contentFilter = $filter;
        $this->resetPage();
    }

    public function toggleFlaggedOnly(): void
    {
        $this->flaggedOnly = ! $this->flaggedOnly;

        if ($this->flaggedOnly) {
            $this->status = null;
        }

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
            'contentFilter',
        ]);

        $this->resetPage();
    }

    public function render()
    {
        $query = Writeup::forReviewQueue()
            ->filterByYear($this->year)
            ->filterByCollege($this->collegeId)
            ->filterByStatus($this->status)
            ->flaggedOnly($this->flaggedOnly)
            ->searchStudent($this->search);

        $writeups = $this->applyContentFilter($query)
            ->orderedForReviewQueue()
            ->paginate($this->perPage);

        return view('livewire.writeup-review-queue', [
            'writeups' => $writeups,
            'years' => $this->getYears(),
            'colleges' => $this->getColleges(),
            'statuses' => $this->getStatuses(),
            'statusCounts' => $this->getStatusCounts(),
            'collegeCounts' => $this->getCollegeCounts(),
            'contentCounts' => $this->getContentCounts(),
            'totalCount' => $this->applyContentFilter($this->getBaseQuery())->count(),
            'canProofread' => $this->canProofreadWriteups(),
        ]);
    }

    private function getBaseQuery(): Builder
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

    private function applyContentFilter(Builder $query, ?string $filter = null): Builder
    {
        $filter = $filter ?? $this->contentFilter;

        if (! in_array($filter, self::VALID_CONTENT_FILTERS, true)) {
            $filter = 'all';
        }

        return match ($filter) {
            'over_300' => $this->applyOver300Filter($query),
            'has_emoji' => $this->applyEmojiFilter($query),
            'has_profanity' => $this->applyProfanityFilter($query),
            default => $query,
        };
    }



    public function markReviewedFromQueue(int $writeupId): void
    {
        if ($this->blockIfCannotProofread()) {
            return;
        }

        $writeup = Writeup::with('studentInfo')->findOrFail($writeupId);

        if ($writeup->review_status === 'reviewed' || $writeup->is_done) {
            session()->flash('error', 'This writeup is already reviewed.');
            return;
        }

        if (
            $writeup->locked_by
            && ! $writeup->lockExpired()
            && (int) $writeup->locked_by !== (int) auth()->id()
        ) {
            session()->flash(
                'error',
                'This writeup is currently being reviewed by another staff member.'
            );

            return;
        }

        $plainText = trim(strip_tags(
            $writeup->edited_writeup
            ?: $writeup->writeup
            ?: ''
        ));

        if ($plainText === '') {
            session()->flash(
                'error',
                'This writeup has no content to review.'
            );

            return;
        }

        if (mb_strlen($plainText) > 500) {
            session()->flash(
                'error',
                'This writeup is over 500 characters and must be reviewed manually.'
            );

            return;
        }

        if (
            preg_match(
                '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u',
                $plainText
            ) === 1
        ) {
            session()->flash(
                'error',
                'This writeup contains emojis and must be reviewed manually.'
            );

            return;
        }

        $student = $writeup->studentInfo;

        $studentName = $student?->formatted_full_name
            ?? trim(
                ($student->last_name ?? '')
                .', '
                .($student->first_name ?? ''),
                ', '
            )
            ?: 'Unknown student';

        $reviewerName = auth()->user()?->name ?? 'Unknown admin';

        /*
        * remember whether this writeup was still managed by a generic
        * writeup or bulk-create batch before detaching it.
        */
        $wasBulkManaged =
            $writeup->generic_writeup_id !== null
            || $writeup->bulk_writeup_batch_id !== null;

        $oldValues = $writeup->only([
            'generic_writeup_id',
            'bulk_writeup_batch_id',
            'edited_writeup',
            'proofreader_id',
            'is_done',
            'review_status',
            'date_of_proofread',
            'reviewed_at',
            'locked_by',
            'locked_at',
        ]);

        $writeup->update([
            'edited_writeup' => $writeup->edited_writeup
                ?: $writeup->writeup,

            'proofreader_id' => auth()->id(),
            'is_done' => true,
            'review_status' => 'reviewed',
            'date_of_proofread' => now()->toDateString(),
            'reviewed_at' => now(),

            'locked_by' => null,
            'locked_at' => null,

            /*
            * It has now been individually reviewed, so it becomes
            * independent from the generic template and bulk batch.
            */
            'generic_writeup_id' => null,
            'bulk_writeup_batch_id' => null,
        ]);

        $writeup->refresh();

        AuditLogger::record(
            module: 'writeup_review',
            action: 'marked_reviewed_from_queue',
            description: $wasBulkManaged
                ? "{$reviewerName} marked the writeup of {$studentName} as reviewed from the queue and detached it from its bulk-generated source."
                : "{$reviewerName} marked the writeup of {$studentName} as reviewed from the queue.",
            model: $writeup,
            oldValues: $oldValues,
            newValues: $writeup->only([
                'generic_writeup_id',
                'bulk_writeup_batch_id',
                'edited_writeup',
                'proofreader_id',
                'is_done',
                'review_status',
                'date_of_proofread',
                'reviewed_at',
                'locked_by',
                'locked_at',
            ])
        );

        session()->flash(
            'success',
            $wasBulkManaged
                ? 'Writeup marked as reviewed and detached from its bulk-generated source.'
                : 'Writeup marked as reviewed.'
        );
    }




    private function writeupContentSql(): string
    {
        return "TRIM(COALESCE(NULLIF(edited_writeup, ''), writeup, ''))";
    }

    private function applyOver300Filter(Builder $query): Builder
    {
        return $query->whereRaw(
            'CHAR_LENGTH(' . $this->writeupContentSql() . ') > ?',
            [300]
        );
    }

    private function applyEmojiFilter(Builder $query): Builder
    {
        return $query->whereRaw(
            $this->writeupContentSql() . ' REGEXP ?',
            [$this->emojiRegex()]
        );
    }

    private function emojiRegex(): string
    {
        return '[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]';
    }

    private function applyProfanityFilter(Builder $query): Builder
    {
        $terms = $this->profanityTerms();

        if (empty($terms)) {
            return $query->whereRaw('1 = 0');
        }

        $contentSql = 'LOWER(' . $this->writeupContentSql() . ')';

        return $query->where(function ($query) use ($terms, $contentSql) {
            foreach ($terms as $term) {
                $query->orWhereRaw(
                    $contentSql . ' LIKE ?',
                    ['%' . mb_strtolower($term) . '%']
                );
            }
        });
    }

    private function profanityTerms(): array
    {
        return collect(config('cyb.profanity_terms', [
            // English
            'fuck',
            'shit',
            'bitch',
            'asshole',

            // Common Filipino/Cebuano profanity examples
            'puta',
            'putangina',
            'tangina',
            'gago',
            'ulol',
            'tarantado',
            'yawa',
        ]))
            ->map(fn ($term) => trim((string) $term))
            ->filter()
            ->unique()
            ->values()
            ->all();
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

        $baseQuery = $this->applyContentFilter($this->getBaseQuery());

        foreach (array_keys($this->getStatuses()) as $status) {
            $counts[$status] = (clone $baseQuery)
                ->where('review_status', $status)
                ->count();
        }

        $counts['all'] = (clone $baseQuery)->count();

        $counts['flagged_only'] = (clone $baseQuery)
            ->where('is_flagged', true)
            ->count();

        return $counts;
    }

    private function getCollegeCounts(): array
    {
        $counts = [];
        $baseQuery = $this->applyContentFilter($this->getBaseQuery());

        $colleges = College::query()->pluck('id');

        foreach ($colleges as $collegeId) {
            $counts[$collegeId] = (clone $baseQuery)
                ->whereHas('studentInfo', function ($query) use ($collegeId) {
                    $query->where('college_id', $collegeId);
                })
                ->count();
        }

        return $counts;
    }

    private function getContentCounts(): array
    {
        $baseQuery = $this->getBaseQuery();

        return [
            'all' => (clone $baseQuery)->count(),
            'over_300' => $this->applyContentFilter((clone $baseQuery), 'over_300')->count(),
            'has_emoji' => $this->applyContentFilter((clone $baseQuery), 'has_emoji')->count(),
            'has_profanity' => $this->applyContentFilter((clone $baseQuery), 'has_profanity')->count(),
        ];
    }

    public function toggleFlag(int $writeupId): void
    {
        if ($this->blockIfCannotProofread()) {
            return;
        }

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
        if ($this->blockIfCannotProofread()) {
            return;
        }

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
        if ($this->blockIfCannotProofread()) {
            return;
        }

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