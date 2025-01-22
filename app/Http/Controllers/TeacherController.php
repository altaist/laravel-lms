<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\TeamService;
use App\Services\StudentService;
use App\Services\PaymentService;
use App\Services\ActivityService;

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

        $teacher = ['name' => 'John Doe'];

        return $this->inertia('Lk/LkTeacher', [
            'teacher' => $teacher,
            'teams' => $teams,
            'students' => $users,
            'activities' => $activities,
            'payments' => $payments,
        ]);
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


    public function teamDetails(int $teamId)
    {
        $teamService = TeamService::make();
        $team = $teamService->getTeamById($teamId);
        $users = $teamService->getTeamUsers($teamId);

        $activityService = ActivityService::make();
        $activities = $activityService->getTeamActivities($teamId);

        $paymentService = PaymentService::make();
        $payments = $paymentService->getTeamPayments($teamId);

        return $this->inertia('Teacher/TeamDetails', [
            'team' => $team,
            'users' => $users,
            'activities' => $activities,
            'payments' => $payments
        ]);


    }

    public function studentDetails(int $studentId)
    {
        $studentService = StudentService::make();
        $student = $studentService->getStudentWithTeams($studentId);

        return $this->inertia('Lk/StudentDetails', [
            'student' => $student
        ]);
    }
}
