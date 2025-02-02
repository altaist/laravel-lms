<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Services\TeamService;
use App\Services\ScheduleService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected $teamService;
    protected $scheduleService;

    public function __construct(
        TeamService $teamService,
        ScheduleService $scheduleService
    ) {
        $this->teamService = $teamService;
        $this->scheduleService = $scheduleService;
    }

    public function addStudent(Request $request)
    {
        $validated = $request->validate([
            'teamId' => 'required|integer',
            'studentId' => 'required|integer'
        ]);

        $this->teamService->addUserToTeam(
            $validated['teamId'],
            $validated['studentId']
        );

        return redirect()->back();
    }

    public function removeStudent(Request $request)
    {
        $validated = $request->validate([
            'teamId' => 'required|integer',
            'studentId' => 'required|integer'
        ]);

        $this->teamService->removeUserFromTeam(
            $validated['teamId'],
            $validated['studentId']
        );

        return redirect()->back();
    }

    public function updateSchedule(Request $request, Team $team)
    {
        $validated = $request->validate([
            'schedule_days' => 'required|array',
            'schedule_days.*.day_of_week' => 'required|integer|between:1,7',
            'schedule_days.*.start_time' => 'required|date_format:H:i',
            'schedule_days.*.end_time' => 'required|date_format:H:i|after:schedule_days.*.start_time',
        ], [
            'schedule_days.required' => 'Поле расписания обязательно для заполнения.',
            'schedule_days.array' => 'Поле расписания должно быть массивом.',
            'schedule_days.*.day_of_week.required' => 'День недели обязательно для каждого расписания.',
            'schedule_days.*.day_of_week.integer' => 'День недели должен быть целым числом.',
            'schedule_days.*.day_of_week.between' => 'День недели должен быть между 1 и 7.',
            'schedule_days.*.start_time.required' => 'Время начала обязательно для каждого расписания.',
            'schedule_days.*.start_time.date_format' => 'Время начала должно быть в формате ЧЧ:ММ.',
            'schedule_days.*.end_time.required' => 'Время окончания обязательно для каждого расписания.',
            'schedule_days.*.end_time.date_format' => 'Время окончания должно быть в формате ЧЧ:ММ.',
            'schedule_days.*.end_time.after' => 'Время окончания должно быть позже времени начала.',
        ]);

        try {
            $result = $this->scheduleService->updateSchedule($team, $validated['schedule_days']);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка при обновлении расписания'], 500);
        }
    }

    public function getAllSchedules()
    {
        $teams = Team::with('schedule.days')->get();

        return response()->json($teams);
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,' . $team->id,
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Название команды обязательно',
            'name.max' => 'Название команды не должно превышать 255 символов',
            'name.unique' => 'Команда с таким названием уже существует',
            'description.max' => 'Описание не должно превышать 1000 символов',
        ]);

        try {
            $updatedTeam = $this->teamService->updateTeam($team->id, $validated);
            return response()->json([
                'message' => 'Команда успешно обновлена',
                'team' => $updatedTeam
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при обновлении команды',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }
} 