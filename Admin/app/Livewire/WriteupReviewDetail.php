<?php

namespace App\Livewire;

use App\Models\Writeup;
use Illuminate\Support\Str;
use Livewire\Component;
use App\Support\AuditLogger;

class WriteupReviewDetail extends Component
{
    public Writeup $writeup;
    public string $editedWriteup = '';
    public int $maxCharacters = 500;
    public string $activePanel = 'review';


    public function setActivePanel(string $panel): void
    {
        if (! in_array($panel, ['original', 'review'], true)) {
            return;
        }

        $this->activePanel = $panel;
    }



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

    public function mount(Writeup $writeup): void
    {
        $this->writeup = $writeup->load([
            'studentInfo.college',
            'studentInfo.program',
            'studentInfo.major',
            'proofreader',
            'lockedBy',
            'flaggedBy',
            'genericWriteup',
            'bulkWriteupBatch',
        ]);

        $this->editedWriteup = $this->writeup->edited_writeup
            ?: $this->writeup->writeup
            ?: '';
    }

    public function startReview(): void
    {
        $this->activePanel = 'review';
        if ($this->blockIfCannotProofread()) {
            return;
        }
        $this->writeup->refresh();

        if (
            $this->writeup->locked_by &&
            ! $this->writeup->lockExpired() &&
            (int) $this->writeup->locked_by !== (int) auth()->id()
        ) {
            session()->flash('error', 'This writeup is already being reviewed by another staff member.');
            return;
        }

        $this->writeup->update([
            'locked_by' => auth()->id(),
            'locked_at' => now(),
            'review_status' => 'in_review',
        ]);

        $this->refreshWriteup();

        session()->flash('success', 'Writeup review started.');
    }

    public function saveChanges(): void
    {
        if ($this->blockIfCannotProofread()) {
            return;
        }
        if (! $this->canEdit()) {
            session()->flash('error', 'You can only save changes to writeups you are currently reviewing.');
            return;
        }

        if (! $this->validateEditedWriteup()) {
            return;
        }

        $this->writeup->update([
            'edited_writeup' => $this->editedWriteup,
            'proofreader_id' => auth()->id(),
        ]);

        $this->refreshWriteup();

        session()->flash('success', 'Writeup changes saved.');
    }

    public function markReviewed(): void
    {
        if ($this->blockIfCannotProofread()) {
            return;
        }

        if (! $this->canEdit()) {
            session()->flash(
                'error',
                'You can only mark writeups as reviewed if you are currently reviewing them.'
            );

            return;
        }

        if (! $this->validateEditedWriteup()) {
            return;
        }

        $this->writeup->refresh();

        /*
        * Recheck the lock after refreshing in case it expired or was
        * changed while the reviewer had the page open.
        */
        if (! $this->canEdit()) {
            session()->flash(
                'error',
                'Your review lock has expired. Please start the review again.'
            );

            return;
        }

        $student = $this->writeup->studentInfo;

        $studentName = $student?->formatted_full_name
            ?? trim(
                ($student->last_name ?? '')
                .', '
                .($student->first_name ?? ''),
                ', '
            )
            ?: 'Unknown student';

        $reviewerName = auth()->user()?->name ?? 'Unknown admin';

        $wasBulkManaged =
            $this->writeup->generic_writeup_id !== null
            || $this->writeup->bulk_writeup_batch_id !== null;

        $oldValues = $this->writeup->only([
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

        $this->writeup->update([
            'edited_writeup' => $this->editedWriteup,
            'proofreader_id' => auth()->id(),
            'is_done' => true,
            'review_status' => 'reviewed',
            'date_of_proofread' => now()->toDateString(),
            'reviewed_at' => now(),

            'locked_by' => null,
            'locked_at' => null,

            /*
            * The writeup has now been individually re-reviewed.
            * It must no longer be affected by template updates or Undo.
            */
            'generic_writeup_id' => null,
            'bulk_writeup_batch_id' => null,
        ]);

        $this->refreshWriteup();

        AuditLogger::record(
            module: 'writeup_review',
            action: 'marked_reviewed',
            description: $wasBulkManaged
                ? "{$reviewerName} marked the writeup of {$studentName} as reviewed and detached it from its bulk-generated source."
                : "{$reviewerName} marked the writeup of {$studentName} as reviewed.",
            model: $this->writeup,
            oldValues: $oldValues,
            newValues: $this->writeup->only([
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
                ? 'Writeup marked as reviewed and protected from bulk Undo.'
                : 'Writeup marked as reviewed.'
        );
    }

    public function releaseReview(): void
    {
        if ($this->blockIfCannotProofread()) {
            return;
        }

        $this->writeup->refresh();

        if ((int) $this->writeup->locked_by !== (int) auth()->id()) {
            session()->flash('error', 'You can only release writeups that you are currently reviewing.');
            return;
        }

        $this->writeup->update([
            'locked_by' => null,
            'locked_at' => null,
            'review_status' => $this->writeup->is_flagged ? 'flagged' : 'pending',
        ]);

        $this->refreshWriteup();

        session()->flash('success', 'Writeup review released.');
    }

    public function toggleFlag(): void
    {

        if ($this->blockIfCannotProofread()) {
            return;
        }

        $this->writeup->refresh();

        if ($this->writeup->is_flagged) {
            $this->writeup->update([
                'is_flagged' => false,
                'flag_reason' => null,
                'flagged_by' => null,
                'flagged_at' => null,
                'review_status' => $this->writeup->is_done
                    ? 'reviewed'
                    : ($this->writeup->locked_by ? 'in_review' : 'pending'),
            ]);

            $this->refreshWriteup();

            session()->flash('success', 'Flag removed.');
            return;
        }

        $this->writeup->update([
            'is_flagged' => true,
            'flag_reason' => null,
            'flagged_by' => auth()->id(),
            'flagged_at' => now(),
        ]);

        $this->refreshWriteup();

        session()->flash('success', 'Writeup flagged for attention.');
    }

    public function canEdit(): bool
    {
        return $this->writeup->locked_by &&
            (int) $this->writeup->locked_by === (int) auth()->id() &&
            ! $this->writeup->lockExpired();
    }

    public function canStartReview(): bool
    {
        return ! $this->writeup->locked_by || $this->writeup->lockExpired();
    }

    private function validateEditedWriteup(): bool
    {
        $this->resetErrorBag('editedWriteup');

        $plainText = $this->plainText($this->editedWriteup);

        if ($plainText === '') {
            $this->addError('editedWriteup', 'The reviewed writeup is required.');
            return false;
        }

        if (mb_strlen($plainText) > $this->maxCharacters) {
            $this->addError('editedWriteup', 'The reviewed writeup must not exceed '.$this->maxCharacters.' characters.');
            return false;
        }

        if ($this->containsEmoji($plainText)) {
            $this->addError('editedWriteup', 'Emojis are not allowed in the reviewed writeup.');
            return false;
        }

        return true;
    }

    private function containsEmoji(string $value): bool
    {
        return preg_match('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $value) === 1;
    }

    private function plainText(?string $value): string
    {
        $value = strip_tags($value ?? '');
        $value = html_entity_decode($value);

        $value = preg_replace('/\*\*(.*?)\*\*/s', '$1', $value);
        $value = preg_replace('/\*(.*?)\*/s', '$1', $value);
        $value = preg_replace('/_(.*?)_/s', '$1', $value);
        $value = preg_replace('/`(.*?)`/s', '$1', $value);

        return trim($value);
    }

    private function refreshWriteup(): void
    {
        $this->writeup = $this->writeup->fresh([
            'studentInfo.college',
            'studentInfo.program',
            'studentInfo.major',
            'proofreader',
            'lockedBy',
            'flaggedBy',
            'genericWriteup',
            'bulkWriteupBatch',
        ]);
    }

    public function getCharacterCountProperty(): int
    {
        return mb_strlen($this->plainText($this->editedWriteup));
    }

    public function getRemainingCharactersProperty(): int
    {
        return max(0, $this->maxCharacters - $this->characterCount);
    }

    public function getRenderedEditedWriteupProperty(): string
    {
        return Str::markdown($this->editedWriteup ?: '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function getRenderedOriginalWriteupProperty(): string
    {
        return Str::markdown(e($this->writeup->writeup ?: ''), [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function render()
    {
        return view('livewire.writeup-review-detail', [
            'canEdit' => $this->canEdit(),
            'canStartReview' => $this->canStartReview(),
            'canProofread' => $this->canProofreadWriteups(),
        ]);
    }
}