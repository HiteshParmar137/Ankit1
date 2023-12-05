<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\PunchLogs;
use App\Observers\ProjectObserver;
use App\Observers\PunchLogObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        PunchLogs::observe(PunchLogObserver::class);
        Project::observe(ProjectObserver::class);
    }
}
