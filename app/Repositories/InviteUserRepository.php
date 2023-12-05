<?php

namespace App\Repositories;

use App\Interfaces\InviteUsersRepositoryInterface;
use App\Jobs\InviteUserJob;
use App\Models\InviteUser;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Role;

class InviteUserRepository extends BaseRepository implements
InviteUsersRepositoryInterface
{
    // get all invite user.
    public function getAllData($data)
    {
        $queryData = InviteUser::GetTextSearch($data)->orderBy('id', 'desc');
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store invite user
    public function store($data)
    {   
        $inviteUser = InviteUser::create($data);
        $token = Crypt::encrypt($inviteUser->id);
        dispatch(new InviteUserJob($data, $token));
        return $inviteUser;
    }

    // editing the specified resource
    public function edit($inviteUserId)
    {
        return InviteUser::find($inviteUserId);
    }

    // Update the specified resource
    public function update($inviteUserId, array $inviteUserDetails)
    {   
        $inviteUser = tap(InviteUser::find($inviteUserId)->CreatedByItself())->update($inviteUserDetails);
            $token = Crypt::encrypt($inviteUserId);
            dispatch(new InviteUserJob($inviteUserDetails, $token));
        return $inviteUser;

        return InviteUser::whereId($inviteUserId)->update($inviteUserDetails);
    }

    // Delete specified resource
    public function delete($inviteUserId)
    {
        return InviteUser::destroy($inviteUserId);
    }

    // Store user
    public function userRegister($inviteUserId,$data)
    {
        $user = User::create($data);
        $role = Role::where('id', $data['role'])->first();
        $user->assignRole($role);
        $permissions = $role->permissions()->get();
        $user->syncPermissions($permissions);
        InviteUser::destroy($inviteUserId);
        return $user;
    }
}