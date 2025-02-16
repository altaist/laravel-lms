<?php

namespace App\Http\Controllers;

use App\Services\StudentService;
use App\Services\TeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StudentRequest;
use App\Models\Coin;

class StudentController extends BaseController
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function show(User $user)
    {
        return response()->json($user->load('teams'));
    }

    public function store(StudentRequest $request)
    {
        $data = $request->validated();
        
        // Генерируем email если не указан
        if (empty($data['email'])) {
            $data['email'] = Str::random(10) . '@example.com';
        }
        
        // Генерируем имя пользователя если не указано
        if (empty($data['name'])) {
            $lastName = $data['person']['lastName'] ?? '';
            $firstName = $data['person']['firstName'] ?? '';
            $data['name'] = trim($firstName . ' ' . $lastName);
        }

        // Генерируем пароль если не указан
        if (empty($data['password'])) {
            $randomPassword = Str::random(10); // Генерируем случайный пароль
            $data['password'] = Hash::make($randomPassword);
            // Можно добавить логику отправки пароля пользователю (email, SMS и т.д.)
        }

        $user = $this->userService->createOrUpdate($data);
        return response()->json($user->load('teams'), 201);
    }

    public function update(StudentRequest $request, User $user)
    {
        $data = $request->validated();
        
        // Сохраняем старый email если новый не указан
        if (empty($data['email'])) {
            $data['email'] = $user->email;
        }
        
        // Сохраняем старое имя если новое не указано
        if (empty($data['name'])) {
            $data['name'] = $user->name;
        }

        // Если пароль не указан, оставляем старый
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->userService->createOrUpdate($data, $user);
        return response()->json($user->load('teams'));
    }

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
        $coins = Coin::all();
        dd($coins);

        return inertia('StudentDetails', [
            'teams' => $teams,
            'activities' => $activities,
            'payments' => $payments,
            'coins' => $coins,
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

    public function lk()
    {
        $student = Auth::user()->load([
            'teams.scheduleDays',
            'activities',
            'payments.user',
            'balances.coin',
            'credits'
        ]);

        // Добавляем коины в объект ученика
        $student = $student->toArray();
        $student['coins'] = Coin::all();
        dd($student);

        return $this->inertia('Lk/LkStudent', [
            'student' => $student,
        ]);
    }
} 