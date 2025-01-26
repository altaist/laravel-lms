<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
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
    Route::get('/teacher/students/{studentId}', [TeacherController::class, 'studentDetails'])
        ->name('teacher.student.details');
    Route::get('/teacher/payments', [TeacherController::class, 'payments'])
        ->name('teacher.payments');

    Route::apiResource('users', StudentController::class)->except(['index', 'destroy']);
});

require __DIR__.'/auth.php';
