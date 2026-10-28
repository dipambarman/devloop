<?php

namespace App\Services;

use App\Models\Bug;
use App\Models\BugEvent;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class BugStateMachine
{
    /**
     * Legal status transitions table.
     */
    public const TRANSITIONS = [
        'open' => ['in_progress', 'fixed', 'wontfix'],
        'in_progress' => ['open', 'fixed', 'wontfix'],
        'fixed' => ['open'],
        'wontfix' => ['open'],
    ];

    /**
     * Transition a bug's status with state-machine validation and optimistic locking.
     *
     * @throws ValidationException If transition is illegal
     * @throws ConflictHttpException If version mismatch (concurrent edit)
     */
    public function transition(Bug $bug, string $toStatus, ?User $user = null, ?int $clientVersion = null): Bug
    {
        $currentStatus = $bug->status;

        if ($currentStatus === $toStatus) {
            return $bug;
        }

        $allowed = self::TRANSITIONS[$currentStatus] ?? [];
        if (! in_array($toStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => ["Illegal status transition from '{$currentStatus}' to '{$toStatus}'."],
            ]);
        }

        // Optimistic concurrency check
        if ($clientVersion !== null && $bug->version !== $clientVersion) {
            throw new ConflictHttpException("Conflict: This bug was updated by another teammate (version {$bug->version}). Please refresh and retry.");
        }

        $bug->status = $toStatus;
        $bug->version += 1;
        $bug->save();

        // Append immutable status_changed event
        BugEvent::create([
            'bug_id' => $bug->id,
            'project_id' => $bug->project_id,
            'type' => 'status_changed',
            'from_status' => $currentStatus,
            'to_status' => $toStatus,
            'user_id' => $user?->id,
            'created_at' => now(),
        ]);

        return $bug;
    }
}
