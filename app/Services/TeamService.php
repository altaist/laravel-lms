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
        return Team::findOrFail($id);
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
        return Team::findOrFail($teamId)->users;
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
        return User::whereHas('teams')->get();
    }
} 