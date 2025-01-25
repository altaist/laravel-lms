<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Activity;
use App\Models\Payment;
use App\Models\User;
use App\Models\Team;
use App\Models\Task;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Relation::morphMap([
            'activity' => Activity::class,
            'payment' => Payment::class,
            'user' => User::class,
            'team' => Team::class,
            'task' => Task::class,
        ]);
    }
}
