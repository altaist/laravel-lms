<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ActivityService extends BaseService
{
    public function __construct(
        private readonly BalanceService $balanceService
    ) {}

    public function getAllActivities()
    {
        return Activity::with(['team', 'users'])->get();
    }

    public function getActivityById($id)
    {
        return Activity::findOrFail($id);
    }

    public function createActivity(array $data)
    {
        return Activity::create($data);
    }

    public function updateActivity($id, array $data)
    {
        $activity = $this->getActivityById($id);
        $activity->update($data);
        return $activity;
    }

    public function deleteActivity($id)
    {
        $activity = $this->getActivityById($id);
        return $activity->delete();
    }

    public function addUserToActivity(int $activityId, int $userId)
    {
        $activity = Activity::findOrFail($activityId);
        $activity->users()->attach($userId, [
            'attached_at' => Carbon::now()
        ]);
    }

    public function removeUserFromActivity(int $activityId, int $userId)
    {
        $activity = Activity::findOrFail($activityId);
        $activity->users()->detach($userId);
    }

    public function getActivityUsers(int $activityId)
    {
        return Activity::findOrFail($activityId)->users;
    }

    /**
     * Получить список активностей команды
     * 
     * @param int $teamId
     * @return Collection
     */
    public function getTeamActivities(int $teamId): Collection
    {
        return Activity::where('team_id', $teamId)
            ->orderBy('starting_at', 'desc')
            ->get();
    }

    public function getActivityDetails(int $activityId)
    {
        $activity = Activity::with(['user', 'homeworks' => function($query) {
                $query->with(['student', 'answers']);
            }])
            ->findOrFail($activityId);
        
        $teamStudents = User::whereHas('teams', function($query) use ($activity) {
                $query->where('id', $activity->team_id);
            })
            ->whereHas('role', function($query) {
                $query->where('slug', 'student');
            })
            ->with(['homeworks' => function($query) use ($activityId) {
                $query->where('activity_id', $activityId);
            }])
            ->get();

        return [
            'activity' => $activity,
            'teamStudents' => $teamStudents,
        ];
    }

    /**
     * Получить текущий актуальный activity
     * 
     * @param int $teamId ID команды
     * @return Activity|null
     */
    public function getCurrentActivity(int $teamId): ?Activity
    {
        $now = now();
        
        return Activity::where('team_id', $teamId)
            ->where('starting_at', '<=', $now)
            ->whereRaw('DATE_ADD(starting_at, INTERVAL duration MINUTE) > ?', [$now])
            ->first();
    }

    /**
     * Списать кредиты за активность
     */
    public function deductCreditsForActivity(Activity $activity, User $user, int $creditValue): void
    {
        // Используем отрицательное значение для списания
        $this->balanceService->updateCreditAndBalance(
            creditable: $activity,
            userId: $user->id,
            creditCoinId: 2, // Фиксированный coin_id для кредитов активности
            creditValue: -abs($creditValue), // Гарантируем отрицательное значение
            reasonId: 2 // Предполагаем, что есть reason_id для списания за активность
        );
    }
} 