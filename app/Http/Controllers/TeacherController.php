<?php

namespace App\Http\Controllers;

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
        $teamsData = $teamService->getAllTeams();
        $studentsData = $teamService->getAllTeamUsers();

        $paymentService = PaymentService::make();
        $paymentData = $paymentService->getAllPayments();

        $activityService = ActivityService::make();
        $activityData = $activityService->getAllActivities();

        $data = [
            "teams" => $teamsData,
            "students" => $studentsData,
            "activities" => $activityData,
            "payments" => $paymentData,
        ];
        return $this->inertia('Wellcome', $data);
    }

    public function allTeams()
    {
        $teamsService = TeamService::make();
        $teamsData = $teamsService->getAllTeams();

        $data = [
            "teams" => $teamsData,
        ];

        return $this->inertia('Teacher/Lk', $data);
    }

    public function allStudents()
    {
        $studentService = StudentService::make();
        $studentsData = $studentService->getAllStudents();
        $data = ["students" => $studentsData];
        return $this->inertia('Teacher/Students', $data);
    }

    public function allLessons()
    {
        $activityService = ActivityService::make();
        $activityData = $activityService->getAllActivities();
        $data = ["activities" => $activityData];
        return $this->inertia('Teacher/Lessons', $data);
    }
}
