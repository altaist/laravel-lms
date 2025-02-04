<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Enums\CreditReasonEnum;

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
        return Activity::with(['team', 'users'])->findOrFail($id);
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

    public function startActivity($id)
    {
        $activity = $this->getActivityById($id);
        $activity->update([
            'started_at' => now(),
            'status' => Activity::STATUS_STARTED
        ]);
        return $activity;
    }

    public function stopActivity($id)
    {
        $activity = $this->getActivityById($id);
        $activity->update([
            'finished_at' => now(),
            'status' => Activity::STATUS_FINISHED
        ]);
        return $activity;
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
        return Activity::findOrFail($activityId)->load('users.teams')->users;
    }

    /**
     * Получить список активностей команды
     * 
     * @param int $teamId
     * @return Collection
     */
    public function getTeamActivities(int $teamId): Collection
    {
        return Activity::with(['users.balances', 'users.teams'])->where('team_id', $teamId)
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
            reasonId: CreditReasonEnum::LESSON->value // Используем код причины "Занятие"
        );
    }

    public function restartActivity($id)
    {
        DB::beginTransaction();
        try {
            $activity = $this->getActivityById($id);
            
            // Отменяем списания кредитов
            DB::table('credits')
                ->where('creditable_type', Activity::class)
                ->where('creditable_id', $activity->id)
                ->delete();
                
            // Сбрасываем статус и временные метки
            $activity->update([
                'status' => Activity::STATUS_PENDING,
                'started_at' => null,
                'finished_at' => null
            ]);
            
            DB::commit();
            return $activity;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Добавить нескольких пользователей к активности
     */
    public function addUsersToActivity(int $activityId, array $userIds): void
    {
        $activity = Activity::findOrFail($activityId);
        $now = Carbon::now();
        
        $attachData = array_fill_keys($userIds, ['attached_at' => $now]);
        $activity->users()->attach($attachData);
    }

    /**
     * Удалить нескольких пользователей из активности
     */
    public function removeUsersFromActivity(int $activityId, array $userIds): void
    {
        $activity = Activity::findOrFail($activityId);
        $activity->users()->detach($userIds);
    }
} 