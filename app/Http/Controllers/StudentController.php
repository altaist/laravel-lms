<?php

namespace App\Http\Controllers;

use App\Services\StudentService;
use App\Services\TeamService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function getTeams(Request $request)
    {
        $studentService = StudentService::make(); // Инициализация локальной переменной
        $studentId = $request->user()->id; // Получаем ID текущего пользователя
        return response()->json($studentService->getStudentWithTeams($studentId));
    }

    public function getPayments(Request $request)
    {
        $studentService = StudentService::make(); // Инициализация локальной переменной
        $studentId = $request->user()->id; // Получаем ID текущего пользователя
        // Логика получения платежей
        return response()->json($studentService->getPayments($studentId));
    }

    public function getActivities(Request $request)
    {
        $studentService = StudentService::make(); // Инициализация локальной переменной
        $studentId = $request->user()->id; // Получаем ID текущего пользователя
        // Логика получения занятий
        return response()->json($studentService->getActivities($studentId));
    }

    public function showStudentDetails(Request $request)
    {
        $studentService = StudentService::make(); // Создание с использованием make
        $studentId = $request->user()->id; // Получаем ID текущего пользователя

        $teams = $studentService->getStudentWithTeams($studentId);
        $activities = $studentService->getActivities($studentId);
        $payments = $studentService->getPayments($studentId);

        return inertia('StudentDetails', [
            'teams' => $teams,
            'activities' => $activities,
            'payments' => $payments,
        ]);
    }

    public function toggleTeamMembership(Request $request, $studentId, $teamId)
    {
        $teamsService = TeamService::make();
        
        if ($request->isMethod('post')) {
            // Добавление в группу
            $teamsService->addUserToTeam($teamId, $studentId);
            return response()->json(['message' => 'Successfully added to team']);
        } else {
            // Удаление из группы
            $teamsService->removeUserFromTeam($teamId, $studentId);
            return response()->json(['message' => 'Successfully removed from team']);
        }
    }
} 