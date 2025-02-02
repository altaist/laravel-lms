<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityRequest;
use App\Models\Activity;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService
    ) {}

    /**
     * Получить список всех активностей
     */
    public function index(Request $request): JsonResponse
    {
        $teamId = $request->query('team_id');
        $activities = $this->activityService->getTeamActivities($teamId);
        return response()->json($activities);
    }

    /**
     * Получить конкретную активность
     */
    public function show(int $id): JsonResponse
    {
        $activity = $this->activityService->getActivityById($id);
        return response()->json($activity);
    }

    /**
     * Создать новую активность
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'starting_at' => 'required|date',
            'duration' => 'required|integer|min:1'
        ]);

        $activity = $this->activityService->createActivity($validated);
        return response()->json($activity, 201);
    }

    /**
     * Обновить активность
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'sometimes|exists:teams,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'info' => 'nullable|array',
            'starting_at' => 'sometimes|date',
            'duration' => 'sometimes|integer|min:1',
        ], [
            'name.required' => 'Название занятия обязательно для заполнения',
            'name.string' => 'Название должно быть текстом',
            'name.max' => 'Название не должно превышать 255 символов',
        ]);

        $activity = $this->activityService->updateActivity($id, $validated);
        return response()->json($activity);
    }

    /**
     * Удалить активность
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $activity = Activity::findOrFail($id);
            
            // Проверяем, что активность еще не началась
            if ($activity->started_at || $activity->finished_at) {
                return response()->json([
                    'message' => 'Нельзя удалить начатое или завершенное занятие'
                ], 422);
            }
            
            $activity->delete();
            
            return response()->json([
                'message' => 'Занятие успешно удалено'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при удалении занятия'
            ], 500);
        }
    }

    /**
     * Обработать активность
     */
    public function processActivity(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'deduct_credits' => 'required|boolean',
            'user_id' => 'required|exists:users,id',
            'credit_value' => 'required_if:deduct_credits,true|integer|min:1'
        ]);

        $activity = Activity::findOrFail($id);
        $user = User::findOrFail($validated['user_id']);

        if ($validated['deduct_credits']) {
            $this->activityService->deductCreditsForActivity(
                activity: $activity,
                user: $user,
                creditValue: $validated['credit_value']
            );
        }

        // Здесь можно добавить дополнительную логику обработки активности
        
        return response()->json([
            'message' => 'Activity processed successfully',
            'activity' => $activity
        ]);
    }

    /**
     * Получить детали активности
     */
    public function getDetails(int $id): JsonResponse
    {
        $details = $this->activityService->getActivityDetails($id);
        return response()->json($details);
    }

    /**
     * Получить активности команды
     */
    public function getTeamActivities(int $teamId): JsonResponse
    {
        $activities = $this->activityService->getTeamActivities($teamId);
        return response()->json($activities);
    }

    /**
     * Начать активность
     */
    public function start(int $id): JsonResponse
    {
        $activity = Activity::findOrFail($id);
        $activity->update([
            'started_at' => now()
        ]);
        
        return response()->json($activity);
    }

    /**
     * Добавить ученика к активности
     */
    public function addStudent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'activityId' => 'required|exists:activities,id',
            'studentId' => 'required|exists:users,id'
        ]);

        $this->activityService->addUserToActivity(
            $validated['activityId'],
            $validated['studentId']
        );

        return response()->json(['message' => 'Student added successfully']);
    }

    /**
     * Завершить активность
     */
    public function stop(int $id): JsonResponse
    {
        $activity = Activity::with('users')->findOrFail($id);
        
        // Списываем кредиты для всех прикрепленных пользователей
        foreach ($activity->users as $user) {
            $this->activityService->deductCreditsForActivity(
                activity: $activity,
                user: $user,
                creditValue: 1 // Списываем 1 кредит
            );
        }
        
        $activity->update([
            'finished_at' => now()
        ]);
        
        return response()->json([
            'message' => 'Activity stopped successfully',
            'activity' => $activity
        ]);
    }

    /**
     * Удалить ученика из активности
     */
    public function removeStudent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'activityId' => 'required|exists:activities,id',
            'studentId' => 'required|exists:users,id'
        ]);

        $this->activityService->removeUserFromActivity(
            $validated['activityId'],
            $validated['studentId']
        );

        return response()->json([
            'message' => 'Student removed successfully'
        ]);
    }

    /**
     * Перезапустить активность
     */
    public function restart(int $id): JsonResponse
    {
        try {
            $activity = $this->activityService->restartActivity($id);
            
            return response()->json([
                'message' => 'Activity restarted successfully',
                'activity' => $activity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error restarting activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Добавить нескольких учеников к активности
     */
    public function addStudents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'activityId' => 'required|exists:activities,id',
            'studentIds' => 'required|array',
            'studentIds.*' => 'exists:users,id'
        ]);

        $this->activityService->addUsersToActivity(
            $validated['activityId'],
            $validated['studentIds']
        );

        return response()->json(['message' => 'Students added successfully']);
    }

    /**
     * Удалить нескольких учеников из активности
     */
    public function removeStudents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'activityId' => 'required|exists:activities,id',
            'studentIds' => 'required|array',
            'studentIds.*' => 'exists:users,id'
        ]);

        $this->activityService->removeUsersFromActivity(
            $validated['activityId'],
            $validated['studentIds']
        );

        return response()->json([
            'message' => 'Students removed successfully'
        ]);
    }
} 