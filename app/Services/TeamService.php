<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;

class TeamService extends BaseService
{

    public function getAllTeams()
    {
        return Team::all();
    }

    public function getTeamById($id)
    {
        return Team::findOrFail($id)->load('scheduleDays');
    }

    public function createTeam(array $data)
    {
        return Team::create($data);
    }

    public function updateTeam($id, array $data)
    {
        $team = $this->getTeamById($id);
        $team->update($data);
        return $team;
    }

    public function deleteTeam($id)
    {
        $team = $this->getTeamById($id);
        return $team->delete();
    }

    /**
     * Получить список пользователей для всех команд
     */
    public function getAllTeamsWithUsers()
    {
        return Team::with('users')->get();
    }

    /**
     * Получить список пользователей для конкретной команды
     */
    public function getTeamUsers(int $teamId)
    {
        return Team::findOrFail($teamId)
            ->load('users.balances')
            ->users;
    }

    /**
     * Добавить пользователя в команду
     */
    public function addUserToTeam(int $teamId, int $userId)
    {
        $team = Team::findOrFail($teamId);
        $team->users()->attach($userId);
    }

    /**
     * Удалить пользователя из команды
     */
    public function removeUserFromTeam(int $teamId, int $userId)
    {
        $team = Team::findOrFail($teamId);
        $team->users()->detach($userId);
    }

    /**
     * Получить всех пользователей всех команд с информацией о командах
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUsersWithTeams()
    {
        return User::with('teams')->get();
    }

    /**
     * Получить всех пользователей всех команд без дублей
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTeamUsers()
    {
        return User::whereHas('teams')->with('teams', 'payments', 'balances')->get();
    }

    public function getTeamStudents(array $teamIds)
    {
        return User::with('balances')->whereHas('teams', function ($query) use ($teamIds) {
            $query->whereIn('teams.id', $teamIds);
        })->get();
    }

    /**
     * Получить список групп, у которых есть занятия в текущий момент
     * 
     * @param int|null $minutesRange Диапазон в минутах до и после времени занятия
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveTeamsJson(?int $minutesRange = null)
    {
        $currentTime = now();
        $currentDayOfWeek = $currentTime->dayOfWeek ?: 7;
        $currentTimeString = $currentTime->format('H:i');

        $startCheck = $minutesRange
            ? $currentTime->copy()->subMinutes($minutesRange)->format('H:i')
            : $currentTimeString;

        $endCheck = $minutesRange
            ? $currentTime->copy()->addMinutes($minutesRange)->format('H:i')
            : $currentTimeString;

        return Team::where(function ($query) use ($currentDayOfWeek, $startCheck, $endCheck) {
            // MySQL
            if (config('database.default') === 'mysql') {
                $query->whereRaw("
                    JSON_CONTAINS(
                        JSON_EXTRACT(schedule, '$.days'),
                        JSON_ARRAY(?),
                        '$[*][0]'
                    )", [$currentDayOfWeek])
                    ->whereRaw("
                        JSON_SEARCH(
                            JSON_EXTRACT(schedule, '$.days'),
                            'one',
                            ?,
                            NULL,
                            '$[*][0]'
                        ) IS NOT NULL
                        AND JSON_EXTRACT(
                            schedule,
                            CONCAT(
                                SUBSTRING_INDEX(
                                    JSON_SEARCH(
                                        JSON_EXTRACT(schedule, '$.days'),
                                        'one',
                                        ?,
                                        NULL,
                                        '$[*][0]'
                                    ),
                                    '[',
                                    1
                                ),
                                '[1]'
                            )
                        ) <= ?
                        AND JSON_EXTRACT(
                            schedule,
                            CONCAT(
                                SUBSTRING_INDEX(
                                    JSON_SEARCH(
                                        JSON_EXTRACT(schedule, '$.days'),
                                        'one',
                                        ?,
                                        NULL,
                                        '$[*][0]'
                                    ),
                                    '[',
                                    1
                                ),
                                '[2]'
                            )
                        ) >= ?
                    ", [
                        $currentDayOfWeek,
                        $currentDayOfWeek,
                        $endCheck,
                        $currentDayOfWeek,
                        $startCheck
                    ]);
            }
            // PostgreSQL
            elseif (config('database.default') === 'pgsql') {
                $query->whereRaw("
                    schedule->'days' @> ?::jsonb
                    AND EXISTS (
                        SELECT 1
                        FROM jsonb_array_elements(schedule->'days') as day
                        WHERE day->0 = ?::jsonb
                        AND day->1 <= ?::jsonb
                        AND day->2 >= ?::jsonb
                    )
                ", [
                    json_encode([[$currentDayOfWeek]]),
                    $currentDayOfWeek,
                    json_encode($endCheck),
                    json_encode($startCheck)
                ]);
            }
            // SQLite
            else {
                $query->whereRaw("
                    EXISTS (
                        SELECT 1
                        FROM json_each(json_extract(schedule, '$.days'))
                        WHERE json_extract(value, '$[0]') = ?
                        AND json_extract(value, '$[1]') <= ?
                        AND json_extract(value, '$[2]') >= ?
                    )
                ", [
                    $currentDayOfWeek,
                    $endCheck,
                    $startCheck
                ]);
            }
        })->get();
    }

    /**
     * Получить список групп, у которых есть занятия в текущий момент
     * 
     * @param int|null $minutesRange Диапазон в минутах до и после времени занятия
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveTeams(?int $minutesRange = null)
    {
        $currentTime = now();
        $currentDayOfWeek = $currentTime->dayOfWeek ?: 7;

        $query = Team::whereHas('scheduleDays', function ($query) use ($currentTime, $currentDayOfWeek, $minutesRange) {
            $query->where('day_of_week', $currentDayOfWeek);

            if ($minutesRange) {
                $startCheck = $currentTime->copy()->subMinutes($minutesRange)->format('H:i');
                $endCheck = $currentTime->copy()->addMinutes($minutesRange)->format('H:i');

                $query->where('start_time', '<=', $endCheck)
                    ->where('end_time', '>=', $startCheck);
            } else {
                $currentTimeString = $currentTime->format('H:i');

                $query->where('start_time', '<=', $currentTimeString)
                    ->where('end_time', '>=', $currentTimeString);
            }
        });

        return $query->with('scheduleDays')->get();
    }
}
