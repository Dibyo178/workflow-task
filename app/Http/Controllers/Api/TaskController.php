<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(protected WorkflowService $workflow) {}

    // GET /tasks — USER sees own, ADMIN sees all
    public function index(Request $request)
    {
        $query = Task::with('user');

        if (auth()->user()->role !== 'ADMIN') {
            $query->where('user_id', auth()->id());
        }

        // Filtering
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return response()->json($query->latest()->paginate(10));
    }

    // POST /tasks
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task = Task::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'user_id'     => auth()->id(),
            'created_by'  => auth()->id(),
            'updated_by'  => auth()->id(),
            'status'      => 'PENDING',
        ]);

        return response()->json($task, 201);
    }

    // GET /tasks/{id}
    public function show(Task $task)
    {
        $this->authorizeTaskAccess($task);
        return response()->json($task->load('user'));
    }

    // PATCH /tasks/{id} — update title/description only, NOT status directly
    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        // Status change is NOT allowed via this endpoint — use workflow endpoints
        if ($request->has('status')) {
            return response()->json([
                'error' => 'Cannot change status directly. Use the workflow endpoints: /tasks/{id}/start, /complete, /approve, /reject',
            ], 422);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
        ]);

        // Only PENDING or IN_PROGRESS tasks can be edited
        if (!in_array($task->status, ['PENDING', 'IN_PROGRESS'])) {
            return response()->json([
                'error' => 'Only PENDING or IN_PROGRESS tasks can be updated.',
            ], 422);
        }

        $task->update(array_merge($validated, ['updated_by' => auth()->id()]));

        return response()->json($task);
    }

    // DELETE /tasks/{id} — soft delete
    public function destroy(Task $task)
    {
        $this->authorizeTaskAccess($task);
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }

    // PATCH /tasks/{id}/start — PENDING → IN_PROGRESS
    public function start(Task $task)
    {
        $this->authorizeTaskAccess($task);
        return $this->applyTransition($task, 'IN_PROGRESS');
    }

    // PATCH /tasks/{id}/complete — IN_PROGRESS → COMPLETED
    public function complete(Task $task)
    {
        $this->authorizeTaskAccess($task);
        return $this->applyTransition($task, 'COMPLETED');
    }

    // PATCH /tasks/{id}/approve — COMPLETED → APPROVED (ADMIN only)
    public function approve(Task $task)
    {
        return $this->applyTransition($task, 'APPROVED');
    }

    // PATCH /tasks/{id}/reject — COMPLETED → REJECTED (ADMIN only)
    public function reject(Request $request, Task $task)
    {
        if ($request->filled('reason')) {
            $task->update(['rejection_reason' => $request->reason]);
        }
        return $this->applyTransition($task, 'REJECTED');
    }

    // -------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------

    private function applyTransition(Task $task, string $newStatus)
    {
        $result = $this->workflow->transition(
            $task,
            $newStatus,
            auth()->id(),
            auth()->user()->role
        );

        if (!$result['success']) {
            return response()->json(['error' => $result['message']], 422);
        }

        return response()->json([
            'message' => "Task status updated to {$newStatus}",
            'task'    => $result['task'],
        ]);
    }

    private function authorizeTaskAccess(Task $task): void
    {
        $user = auth()->user();
        if ($user->role !== 'ADMIN' && $task->user_id !== $user->id) {
            abort(403, 'You do not have access to this task.');
        }
    }
}