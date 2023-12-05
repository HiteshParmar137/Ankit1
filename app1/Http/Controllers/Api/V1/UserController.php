<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserRequest;
use App\Http\Resources\Api\V1\User\UserDetailResource;
use App\Http\Resources\Api\V1\User\UsersListResource;
use App\Jobs\SendUserRegistrationMailJob;
use App\Models\User;
use App\Service\RolePermission\RolePermissionService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
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
    public function index(UserRequest $request): object
    {
        try {
            $perPage = $request->per_page_records ?? 10;
            $page = $page ?? 1;

            $users = User::select(
                'users.id as id',
                'users.first_name as first_name',
                'users.last_name as last_name',
                'users.email as email',
                'users.phone_number as phone_number',
                'users.phone_number_country_code as phone_number_country_code',
                'users.email_verified_at as email_verified_at',
                'users.status as status',
                'users.can_work_in_aws as can_work_in_aws',
                'roles.name as name',
                'roles.display_name as display_name',
            )
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->withoutSuperAdmin()
            ->filter($request->all())
            ->paginate($perPage);

            $usersResource = UsersListResource::collection($users);

            return $this->paginatedSuccessResponse(200, "Users", $usersResource);

        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request): object
    {
        try {
            $user = Helpers::getLoginUser();
            $roleName = $request->role_name;
            $plainPassword = Helpers::generateStrongPassword();

            $request->merge([
                'password' => Hash::make($plainPassword),
                'email_verified_at' => now()
            ]);

            DB::beginTransaction();

            $user = User::create(
                $request->only([
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                    'phone_number_country_code',
                    'password',
                    'status',
                    'can_work_in_aws',
                    'email_verified_at'
                ])
            );

            $user->userProfile()->create([
                'profile_id' => $request->profile_id
            ]);

            $this->rolePermissionService->assignRoleAndPermissionToUser($user, $roleName);

            DB::commit();

            SendUserRegistrationMailJob::dispatch($user, $plainPassword)->delay(now()->addSeconds(2));

            return $this->successResponse(200, "User added successfully", ['id' => $user->id]);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error($e);
                return $this->errorResponse(500, "Something is wrong");
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function show(int $userId): object
    {
        try {
            $user = User::with('userProfile:id,user_id,profile_id')
                ->select(
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                    'phone_number_country_code',
                    'status',
                    'can_work_in_aws',
                )
                ->find($userId);

            if (!empty($user)) {
                $userDetailsResource = new UserDetailResource($user);

                return $this->successResponse(200, "User details", $userDetailsResource);

            } else {
                return $this->errorResponse(400, "No user found");
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
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request): object
    {
        try {
            $userId = $request->user_id ?? null;
            $roleName = $request->role_name ?? null;

            $user = User::with('userProfile:id,user_id,profile_id')
                ->select(
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                    'phone_number_country_code',
                    'status',
                    'can_work_in_aws'
                )
                ->find($userId);

            if (!empty($user)) {
                DB::beginTransaction();

                tap($user)->update(
                    $request->only([
                        'first_name',
                        'last_name',
                        'email',
                        'phone_number',
                        'phone_number_country_code',
                        'can_work_in_aws'
                    ])
                );

                $user->userProfile()->update([
                    'profile_id' => $request->profile_id
                ]);

                $this->rolePermissionService->updateRoleAndSyncPermissions($user, $roleName);
    
                DB::commit();

                return $this->successResponse(200, "User updated successfully", ['id' => $user->id]);

            } else {
                return $this->errorResponse(400, "No user found");
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId): object
    {
        return $this->errorResponse(503, "Service Unavailable");

        try {
            $user = User::find($userId);

            if (!empty($user)) {
                DB::beginTransaction();

                $user->delete();

                DB::commit();

                return $this->successResponse(200, "User deleted successfully", ['id' => $userId]);

            } else {
                return $this->errorResponse(400, "No user found");
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Update the specified resource status.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function status(UserRequest $request): object
    {
        try {
            $userId = $request->user_id ?? null;

            $user = User::select(
                'id',
                'status',
            )
            ->find($userId);

            if (!empty($user)) {
                DB::beginTransaction();

                tap($user)->update(
                    $request->only([
                        'status'
                    ])
                );

                DB::commit();

                return $this->successResponse(200, "User status updated successfully");

            } else {
                return $this->errorResponse(400, "No user found");
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * @return object<string, mixed[]>
     */
    // public function userTypes(): object
    // {
    //     try {
    //         return $this->successResponse(
    //             200,
    //             "User types",
    //             UserTypes::getList()
    //         );
    //     } catch (Exception $e) {
    //         Log::error($e);
    //         return $this->errorResponse(500, "Something is wrong");
    //     }
    // }

    /**
     * @return object<string, mixed[]>
     */
    // public function userStatuses(): object
    // {
    //     try {
    //         return $this->successResponse(
    //             200,
    //             "User statuses",
    //             UserStatuses::getList()
    //         );
    //     } catch (Exception $e) {
    //         Log::error($e);
    //         return $this->errorResponse(500, "Something is wrong");
    //     }
    // }
}
