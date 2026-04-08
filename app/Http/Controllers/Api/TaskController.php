<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        // USER shudhu nijer task dekhte parbe, ADMIN shob
        if (auth()->user()->role !== 'ADMIN') {
            $query->where('user_id', auth()->id());
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => auth()->id(),
            'status' => 'PENDING'
        ]);

        return response()->json($task, 201);
    }

    // Task Update Method (Status 'COMPLETED' korar jonno eita dorkar)
    public function update(Request $request, Task $task)
    {
        // Shudhu task-er owner ba Admin update korte parbe
        if (auth()->user()->role !== 'ADMIN' && $task->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->update([
            'status' => $request->status ?? $task->status,
            'title' => $request->title ?? $task->title,
            'description' => $request->description ?? $task->description,
            'updated_by' => auth()->id()
        ]);

        return response()->json($task);
    }

    public function approve(Task $task)
    {
        // Enforce state transition: Only COMPLETED can be APPROVED
        if ($task->status !== 'COMPLETED') {
            return response()->json([
                'error' => 'Invalid transition. Only completed tasks can be approved.'
            ], 422);
        }

        $task->update([
            'status' => 'APPROVED',
            'updated_by' => auth()->id()
        ]);

        return response()->json(['message' => 'Task approved successfully']);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task soft deleted']);
    }
}
