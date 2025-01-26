<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ActivityController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('credits', CreditController::class);
Route::get('users/{user}/credits', [CreditController::class, 'getUserCredits']);
Route::get('reasons/{reason}/credits', [CreditController::class, 'getByReason']);
Route::get('credits/creditable/{type}/{id}', [CreditController::class, 'getByCreditable']);
Route::post('/register-without-email', [App\Http\Controllers\Auth\CustomRegisterController::class, 'register']);
Route::post('student/{student_id}/teams/{team_id}', [StudentController::class, 'toggleTeamMembership'])->name('api.student.toggle-team');
Route::delete('student/{student_id}/teams/{team_id}', [StudentController::class, 'toggleTeamMembership'])->name('api.student.toggle-team');

Route::middleware('auth')->group(function () {
    Route::get('/student/teams', [StudentController::class, 'getTeams']);
    Route::get('/student/payments', [StudentController::class, 'getPayments']);
    Route::get('/student/activities', [StudentController::class, 'getActivities']);
    Route::apiResource('activities', ActivityController::class);
    Route::post('activities/{id}/process', [ActivityController::class, 'processActivity']);
    Route::get('activities/{id}/details', [ActivityController::class, 'getDetails']);
    Route::get('teams/{teamId}/activities', [ActivityController::class, 'getTeamActivities']);
    //Route::apiResource('students', StudentController::class)->except(['index', 'destroy']);
});
