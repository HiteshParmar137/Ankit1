<?php

namespace App\Repositories;

use App\Interfaces\RolesRepositoryInterface;
use App\Models\Roles;

class RolesRepository extends BaseRepository implements RolesRepositoryInterface
{
    // get all Role
    public function getAllData($data)
    {
        // return Roles::paginate($data['record_per_page']);

        $queryData = Roles::GetTextSearch($data);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store Role
    public function store($data)
    {
        return Roles::create($data);
    }

    // editing the specified resource
    public function edit($roleId)
    {
        return Roles::find($roleId);
    }

    // Update the specified resource
    public function update($roleId, array $newRoleDetails)
    {
        return Roles::find($roleId)->update($newRoleDetails);
    }

    // Delete specified resource
    public function delete($roleId)
    {
        return Roles::destroy($roleId);
    }
}