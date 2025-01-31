<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ActivityController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/lk/teacher', [TeacherController::class, 'lk'])->name('teacher.lk');
    Route::get('/teacher/students', [TeacherController::class, 'students'])
        ->name('teacher.students');
    Route::get('/teacher/students/{studentId}', [TeacherController::class, 'studentDetails'])
        ->name('teacher.student.details');
    Route::get('/teacher/payments', [TeacherController::class, 'payments'])
        ->name('teacher.payments');
    Route::get('/teacher/teams', [TeacherController::class, 'teams'])->name('teacher.teams');
    Route::get('/teacher/team/{teamId}', [TeacherController::class, 'teamDetails'])->name('teacher.team.details');
    Route::get('/teacher/activity/{activityId}', [TeacherController::class, 'activityDetails'])
        ->name('teacher.activity.details');

    Route::apiResource('users', StudentController::class)->except(['index', 'destroy']);

    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/team/{teamId}', [TeamController::class, 'show'])->name('teams.show');
    Route::post('/team/add-student', [TeamController::class, 'addStudent'])->name('teams.add-student');
    Route::post('/team/remove-student', [TeamController::class, 'removeStudent'])->name('teams.remove-student');
    Route::put('/teams/{team}/schedule', [TeamController::class, 'updateSchedule'])
        ->name('teams.schedule.update');
    Route::get('/teams/schedules', [TeamController::class, 'getAllSchedules'])
        ->name('teams.schedules.all');
    Route::get('/teams/all-schedules',[TeacherController::class, 'schedules'])->name('teams.schedules.all.view');

    Route::post('/activities', [ActivityController::class, 'store'])
        ->name('activities.store');
    Route::put('/activities/{activity}', [ActivityController::class, 'update'])
        ->name('activities.update');
    Route::delete('/activities/{id}', [ActivityController::class, 'destroy'])
        ->name('activities.destroy');
    Route::post('/activities/{activity}/start', [ActivityController::class, 'start'])
        ->name('activities.start');
    Route::post('/activities/{activity}/stop', [ActivityController::class, 'stop'])
        ->name('activities.stop');
    Route::post('/activities/add-students', [ActivityController::class, 'addStudents'])
        ->name('activities.add-students');
    Route::post('/activities/remove-students', [ActivityController::class, 'removeStudents'])
        ->name('activities.remove-students');
    Route::post('/activities/{activity}/restart', [ActivityController::class, 'restart'])
        ->name('activities.restart');
});

require __DIR__.'/auth.php';
