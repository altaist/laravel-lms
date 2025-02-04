<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TeamService;
use App\Services\StudentService;
use App\Services\PaymentService;
use App\Services\ActivityService;
use Illuminate\Support\Facades\Auth;
use App\Services\UserService;

class TeacherController extends BaseController
{
    public function lk()
    {
        $teamService = TeamService::make();
        $teams = $teamService->getAllTeams();
        $users = $teamService->getAllTeamUsers();
        //$users = User::all();

        $paymentService = PaymentService::make();
        $payments = $paymentService->getMonthlyPayments();

        $activityService = ActivityService::make();
        $activities = $activityService->getAllActivities();

        $teacher = ['name' => 'Преподаватель'];

        $data = [
            'teacher' => $teacher,
            'teams' => $teams,
            'students' => $users,
            'activities' => $activities,
            'payments' => $payments,
        ];

        // dd($data);

        return $this->inertia('Lk/LkTeacher', $data);
    }

    public function allTeams()
    {
        $teamService = TeamService::make();
        $teams = $teamService->getAllTeams();

        return $this->inertia('Teacher/Lk', [
            'teams' => $teams,
        ]);
    }

    public function allStudents()
    {
        $teamService = TeamService::make();
        $teams = $teamService->getAllTeams();
        $users = $teamService->getAllTeamUsers();

        return $this->inertia('Teacher/TeamsAndUsers', [
            'teams' => $teams,
            'users' => $users
        ]);
    }

    public function allLessons()
    {
        $activityService = ActivityService::make();
        $activities = $activityService->getAllActivities();
        
        return $this->inertia('Teacher/Lessons', [
            'activities' => $activities
        ]);
    }

    public function teams()
    {
        $teacher = Auth::user();
        $teamService = TeamService::make();
        $teams = $teamService->getAllTeamsWithUsers();

        return $this->inertia('Lk/AllTeams', [
            'teacher' => $teacher,
            'teams' => $teams,
        ]);
    }

    public function schedules()
    {
        $teacher = Auth::user();
        $teamService = TeamService::make();
        $teams = $teamService->getAllTeamsWithUsers();

        return $this->inertia('Lk/AllGroupSchedules', [
            'teacher' => $teacher,
            'teams' => $teams,
        ]);
    }


    public function teamDetails(int $teamId)
    {
        $teamService = TeamService::make();
        $activityService = ActivityService::make();
        $paymentService = PaymentService::make();

        $team = $teamService->getTeamById($teamId);
        $users = $teamService->getTeamUsers($teamId);
        $activities = $activityService->getTeamActivities($teamId);
        $payments = $paymentService->getTeamPayments($teamId);

        return $this->inertia('Lk/TeamDetails', [
            'team' => $team,
            'users' => $users,
            'activities' => $activities,
            'payments' => $payments,
            'allStudents' => User::with('balances')->get() // Добавляем список всех учеников
        ]);
    }

    public function studentDetails(int $studentId)
    {
        $studentService = StudentService::make();
        $teamService = TeamService::make();
        $paymentService = PaymentService::make();
        $student = $studentService->getStudentWithTeams($studentId);
        $teams = $teamService->getAllTeams();
        $activities = $studentService->getActivities($studentId);
        $payments = $paymentService->getUserPayments($studentId);
        
        return $this->inertia('Lk/StudentDetails', [
            'student' => $student,
            'teams' => $teams,
            'activities' => $student['activities'],
            'payments' => $payments
        ]);
    }

    public function payments()
    {
        $teacher = Auth::user();
        $teamService = TeamService::make();
        $teams = $teamService->getAllTeams();
        $teamIds = $teams->pluck('id')->toArray();
        
        $paymentService = PaymentService::make();
        $payments = $paymentService->getTeamsPayments($teamIds);
        
        $students = $teamService->getTeamStudents($teamIds);

        return inertia('Lk/AllPayments', [
            'teacher' => $teacher,
            'teams' => $teams,
            'payments' => $payments,
            'students' => $students,
        ]);
    }

    public function students()
    {
        $teacher = Auth::user();
        $teamService = TeamService::make();
        $teams = $teamService->getAllTeams();
        $students = $teamService->getAllTeamUsers();

        return $this->inertia('Lk/AllStudents', [
            'teacher' => $teacher,
            'teams' => $teams,
            'students' => $students,
        ]);
    }

    public function activityDetails(int $activityId)
    {
        $activityService = ActivityService::make();
        $activity = $activityService->getActivityById($activityId);
        $teamService = TeamService::make();
        
        // Получаем пользователей текущей группы и группы с id=1
        $availableUsers = $teamService->getTeamUsers(1);

        $teamUsers = $teamService->getTeamUsers($activity->team_id);
        
        // Получаем прикрепленных пользователей
        $attachedUsers = $activityService->getActivityUsers($activityId);

        return $this->inertia('Lk/ActivityDetails', [
            'activity' => $activity,
            'team' => $teamService->getTeamById($activity->team_id),
            'attachedUsers' => $attachedUsers,
            'availableUsers' => $availableUsers,
            'teamUsers' => $teamUsers,
        ]);
    }
}
