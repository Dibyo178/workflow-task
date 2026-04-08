<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Task;

class TaskObserver
{
    public function created(Task $task): void
    {
        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'created',
            'model_type' => 'Task',
            'model_id'   => $task->id,
            'old_values' => null,
            'new_values' => $task->toArray(),
            'created_at' => now(),
        ]);
    }

    public function updated(Task $task): void
    {
        $dirty = $task->getDirty(); // Only changed fields

        if (empty($dirty)) return;

        // Determine action label based on status change
        $action = 'updated';
        if (isset($dirty['status'])) {
            $action = match ($dirty['status']) {
                'IN_PROGRESS' => 'started',
                'COMPLETED'   => 'completed',
                'APPROVED'    => 'approved',
                'REJECTED'    => 'rejected',
                default       => 'updated',
            };
        }

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => 'Task',
            'model_id'   => $task->id,
            'old_values' => array_intersect_key($task->getOriginal(), $dirty),
            'new_values' => $dirty,
            'created_at' => now(),
        ]);
    }

    public function deleted(Task $task): void
    {
        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'deleted',
            'model_type' => 'Task',
            'model_id'   => $task->id,
            'old_values' => $task->toArray(),
            'new_values' => null,
            'created_at' => now(),
        ]);
    }
}
