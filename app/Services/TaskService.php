<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

class TaskService
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function saveUserAnswer(User $user, Task $task, array $answer): void
    {
        $user->tasks()->updateExistingPivot($task->id, [
            'answer' => $answer
        ]);
    }

    public function saveTeacherResult(User $user, Task $task, array $result): void
    {
        $user->tasks()->updateExistingPivot($task->id, [
            'result' => $result
        ]);
    }
} 