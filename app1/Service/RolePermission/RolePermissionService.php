<?php

namespace App\Service\RolePermission;

use App\Models\Role;
use App\Models\User;

class RolePermissionService
{
    public function assignRoleAndPermissionToUser(User $user, string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();
        $permissions = $role->permissions;

        $user->assignRole($role);
        $user->givePermissionTo($permissions);
        return true;
    }

    public function syncPermissionsToRoleUsers(mixed $role): bool
    {
        $roleName = $role->name;
        $permissions = $role->permissions;
        $users = User::role($roleName)->get();

        if ($users->count()) {
            foreach ($users as $user) {
                $user->syncPermissions($permissions);
            }
        }
        return true;
    }

    public function updateRoleAndSyncPermissions(User $user, string $roleName)
    {
        $currentUserRole = $user->roles->first();
        $currentUserRoleName = $currentUserRole->name;

        if ($currentUserRoleName != $roleName) {
            
            $role = Role::where('name', $roleName)->first();
            $permissions = $role->permissions;

            $user->removeRole($currentUserRole);
            $user->assignRole($role);
            $user->syncPermissions($permissions);
        }

        return true;
    }
}
