<?php

namespace App\Observers;

use App\Enums\UserTypes;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function created(Permission $permission)
    {
        // $this->assignNewPermissionToSuperAdmin($permission);
    }

    /**
     * Handle the Permission "updated" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function updated(Permission $permission)
    {
        // $this->assignNewPermissionToSuperAdmin($permission);
    }

    /**
     * Handle the Permission "deleted" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function deleted(Permission $permission)
    {
        //
    }

    /**
     * Handle the Permission "restored" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function restored(Permission $permission)
    {
        //
    }

    /**
     * Handle the Permission "force deleted" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function forceDeleted(Permission $permission)
    {
        //
    }

    public function assignNewPermissionToSuperAdmin($permission)
    {
        $superAdminFormattedCaseKey = UserTypes::getFormattedCaseKey(UserTypes::SUPER_ADMIN->value);
        
        $superAdminUser = User::role($superAdminFormattedCaseKey)->first();
        $superAdminRole = Role::where('name', $superAdminFormattedCaseKey)->first();

        if (!empty($superAdminUser)) {
            $superAdminUser->givePermissionTo($permission);
        }
        if (!empty($superAdminRole)) {
            $superAdminRole->givePermissionTo($permission);
        }
        return true;
    }
}
