<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DefaultPermissions;
use App\Enums\PermissionTypes;
use App\Enums\UserTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RoleRequest;
use App\Http\Resources\Api\V1\Role\RoleListResource;
use App\Http\Resources\Api\V1\Role\RolePermissionResource;
use App\Models\Permission;
use App\Models\Role;
use App\Service\RolePermission\RolePermissionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $roles = Role::select(
                    'id',
                    'name',
                    'display_name',
                    'description'
                )
                ->get();

            $roleListResource = RoleListResource::collection($roles);

            return $this->successResponse(200, "Roles", $roleListResource);
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $roleName
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $roleName)
    {
        try {
            $role = Role::where('name', $roleName)->first();

            if (!empty($role)) {
                $allPermissions = Permission::getByCanDisabled(PermissionTypes::CAN_BE_DISABLED->value)->get();

                $request->merge([
                    'request_role' => $role
                ]);

                $rolePermissionResource = RolePermissionResource::collection($allPermissions);

                return $this->successResponse(200, "Permissions", $rolePermissionResource);
            } else {
                return $this->errorResponse(400, "No data found");
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(RoleRequest $request)
    {
        try {
            $newPermissions = $request->new_permissions_name ?? [];
            $roleName = $request->role_name;

            $superAdminRoleFormattedCaseKey = UserTypes::getFormattedCaseKey(UserTypes::SUPER_ADMIN->value);

            if ($roleName != $superAdminRoleFormattedCaseKey) {
                if (!in_array(DefaultPermissions::DASHBOARD_PERMISSION_NAME->value, $newPermissions)) {
                    array_push($newPermissions, DefaultPermissions::DASHBOARD_PERMISSION_NAME->value);
                }
                $role = Role::where('name', $roleName)->first();
                $newDatabasePermissions = Permission::whereIn('name', $newPermissions)->get();
                $role->syncPermissions($newDatabasePermissions);
                $this->rolePermissionService->syncPermissionsToRoleUsers($role);

                return $this->successResponse(200, "Permissions updated successfully");
            } else {
                return $this->errorResponse(400, "Permissions for super admin can not be changed");
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }
}
