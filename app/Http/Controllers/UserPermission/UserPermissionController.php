<?php

namespace App\Http\Controllers\UserPermission;

use App\Models\User;
use App\Models\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserPermissionController extends Controller
{
    public function show(Request $request,$userToken)
    {
        try {
            $userId  = decrypt($userToken);
            $user = User::find($userId);


            $permissions = Permissions::with(['users' => function ($query) use ($user) {
                $query->where('model_has_permissions.model_id', $user->id);
            }])
            ->get();

            $view = \View::make('modal.user_permissions_data', [
                'permissions' => $permissions,
                'user' => $user
            ]);

            $html = $view->render();
            return response()->json(['html' => $html, 'message' => 'Data Fetched Successfully!', 'success' =>  true], 200);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $userToken)
    {

        try {
            DB::beginTransaction();
            $userId  = decrypt($userToken);
            $user = User::find($userId);
            $permissionIds = $request->permissions ?? [];
            $permissions = Permissions::whereIn('id', $permissionIds)->get();

            $user->syncPermissions($permissions);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Permissions updated successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }
}
