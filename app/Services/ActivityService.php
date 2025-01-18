<?php

namespace App\Services;

use App\Models\Activity;
use Carbon\Carbon;

class ActivityService extends BaseService
{
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
} 