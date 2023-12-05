<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Profile;
use App\Models\User;
use App\Observers\PermissionObserver;
use App\Observers\ProfileObserver;
use App\Observers\UserObserver;
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
        // To avoid the migration error "unique key is too long"...
        Schema::defaultStringLength(191);

        // Observer
        User::observe(UserObserver::class);
        Permission::observe(PermissionObserver::class);
        Profile::observe(ProfileObserver::class);
    }
}
