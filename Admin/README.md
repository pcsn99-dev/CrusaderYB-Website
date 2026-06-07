# Admin Writeup Review Module Summary

This module adds the admin-side writeup review workflow for the revamped CYB Admin Portal. It is designed as a mailbox-style review queue where assigned admin staff can browse, review, edit, flag, and mark student-submitted writeups as reviewed.

## Added Features

* Added review queue fields to the existing `writeups` table.
* Added model relationships between:
  * `Writeup`
  * `StudentInfo`
  * `College`
  * `Program`
  * `Major`
  * `User`
* Normalized academic relationships by aligning:
  * `programs.college_id`
  * `majors.program_id`
  * `programs.is_graduate_program`
* Added query scopes for writeup review filtering and ordering.
* Added Livewire-based writeup review queue.
* Added mailbox-style inbox layout for writeups.
* Added filters for:
  * Year / batch
  * College
  * Review status
  * Flagged writeups
  * Student name / ID search
* Added pagination support for the writeup queue.
* Added stable `wire:key` values to fix Livewire action issues after pagination.
* Added flag/unflag action directly from the inbox.
* Added writeup-specific review page.
* Added writeup locking system for multi-reviewer coordination.
* Added ability to start, continue, release, and view writeup reviews.
* Added edited writeup saving.
* Added mark as reviewed action.
* Added character limit validation for reviewed writeups.
* Added emoji restriction for reviewed writeups.
* Added basic markdown-style formatting support for reviewed writeups.
* Added preview rendering for edited writeups.

## Review Queue Behavior

The main writeup review page works like an inbox.

Admin staff can filter writeups by:

```txt
All Writeups
Pending Review
In Review
Reviewed
Flagged
College
Year / Batch
Search keyword
```

Each row displays:

```txt
Student name
University ID
Batch/year
College
Program
Writeup preview
Review status
Flag status
Lock/reviewer status
Last updated date
```

## Review Statuses

The module uses the following review statuses:

```txt
pending
in_review
reviewed
flagged
```

Meaning:

```txt
pending = not yet reviewed
in_review = currently being reviewed by an admin staff
reviewed = already reviewed and completed
flagged = needs attention or another check
```

## Locking / Review Coordination

To prevent multiple staff from editing the same writeup at the same time, the module uses a lock system.

When a staff member starts reviewing a writeup:

```txt
locked_by = current admin user
locked_at = current timestamp
review_status = in_review
```

Rules:

```txt
Unlocked writeups can be started by any reviewer.
Writeups locked by the current user can be edited.
Writeups locked by another user can still be viewed, but not edited.
The reviewer can release the writeup so another staff member can review it.
Expired locks can be claimed again.
```

## Writeup Review Page

The writeup review page allows staff to:

```txt
View student information
View original submitted writeup
Edit reviewed writeup
Preview formatted writeup
Save changes
Mark as reviewed
Flag or unflag the writeup
Release the review lock
```

Only the staff member who currently locked the writeup can edit and submit review changes.

## Character Limit and Emoji Restriction

Reviewed writeups are limited to:

```txt
300 characters
```

The module validates the plain text content before saving or marking as reviewed.

Emojis are not allowed in reviewed writeups to keep the yearbook content clean and layout-safe.

## Formatting

The reviewed writeup supports simple markdown-style formatting:

```txt
**bold**
*italic*
```

The preview renders the formatted output while keeping unsafe HTML stripped.

## Database Note

The module reuses the existing legacy CYB database structure, especially:

```txt
writeups
student_info
colleges
programs
majors
users
```

The previous CYB database SQL file is still required because the revamped project does not yet contain full migrations for the entire legacy schema.

Additional migrations were added only for the fields needed by the new review queue and academic relationship cleanup.

## Academic Relationship Cleanup

The old database used pivot-style tables for relationships that were not truly many-to-many:

```txt
college_program
program_major
```

The updated structure aligns the database more directly:

```txt
College has many Programs
Program has many Majors
StudentInfo belongs to College
StudentInfo belongs to Program
StudentInfo belongs to Major
```

Graduate programs are now handled using:

```txt
programs.is_graduate_program
```

instead of treating Graduate School as a separate college.

Example:

```txt
Master in Information Technology
→ College of Computer Studies
→ is_graduate_program = true
```

## Permissions

The writeup review module uses the existing permission system.

Main permissions involved:

```txt
view-writeups
proofread-writeups
```

Current route access uses:

```txt
view-writeups
```

Review actions should remain restricted to users with the appropriate writeup review/proofreading permission.
