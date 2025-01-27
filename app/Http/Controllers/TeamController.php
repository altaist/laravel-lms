<?php

namespace App\Http\Controllers;

use App\Services\TeamService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
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
} 