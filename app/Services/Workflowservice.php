<?php

namespace App\Services;

use App\Models\Task;

class WorkflowService
{
    /**
     * Valid transitions map:
     * current_status => [allowed_next_statuses]
     */
    private array $transitions = [
        'PENDING'     => ['IN_PROGRESS'],
        'IN_PROGRESS' => ['COMPLETED'],
        'COMPLETED'   => ['APPROVED', 'REJECTED'],
        'APPROVED'    => [], // terminal state
        'REJECTED'    => [], // terminal state
    ];

    /**
     * Who can perform which transitions
     */
    private array $allowedRoles = [
        'PENDING'     => ['IN_PROGRESS' => ['USER', 'ADMIN']],
        'IN_PROGRESS' => ['COMPLETED'   => ['USER', 'ADMIN']],
        'COMPLETED'   => [
            'APPROVED' => ['ADMIN'],
            'REJECTED' => ['ADMIN'],
        ],
    ];

    public function canTransition(Task $task, string $newStatus, string $role): bool
    {
        $currentStatus = $task->status;

        // Check if transition is valid
        if (!isset($this->transitions[$currentStatus])) {
            return false;
        }

        if (!in_array($newStatus, $this->transitions[$currentStatus])) {
            return false;
        }

        // Check if role is allowed for this transition
        $rolesAllowed = $this->allowedRoles[$currentStatus][$newStatus] ?? [];
        return in_array($role, $rolesAllowed);
    }

    public function transition(Task $task, string $newStatus, int $userId, string $role): array
    {
        if (!$this->canTransition($task, $newStatus, $role)) {
            return [
                'success' => false,
                'message' => "Invalid transition: '{$task->status}' → '{$newStatus}' is not allowed for role '{$role}'.",
            ];
        }

        $task->update([
            'status'     => $newStatus,
            'updated_by' => $userId,
        ]);

        return [
            'success' => true,
            'task'    => $task->fresh(),
        ];
    }

    public function getAllowedTransitions(Task $task, string $role): array
    {
        $transitions = $this->transitions[$task->status] ?? [];

        return array_filter($transitions, function ($next) use ($task, $role) {
            return $this->canTransition($task, $next, $role);
        });
    }
}
