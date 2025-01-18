<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\TaskRequest;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    public function index(): JsonResponse
    {
        $tasks = Task::with('users')->paginate();
        return response()->json($tasks);
    }

    public function store(TaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create($request->validated());
        return response()->json($task, 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json($task->load('users'));
    }

    public function update(TaskRequest $request, Task $task): JsonResponse
    {
        $task = $this->taskService->update($task, $request->validated());
        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->delete($task);
        return response()->json(null, 204);
    }

    public function submitAnswer(Request $request, Task $task): JsonResponse
    {
        $this->taskService->saveUserAnswer(
            $request->user(),
            $task,
            $request->validate(['answer' => 'required|array'])['answer']
        );
        return response()->json(['message' => 'Answer submitted']);
    }

    public function submitResult(Request $request, Task $task): JsonResponse
    {
        $this->taskService->saveTeacherResult(
            $request->user(),
            $task,
            $request->validate(['result' => 'required|array'])['result']
        );
        return response()->json(['message' => 'Result submitted']);
    }
} 