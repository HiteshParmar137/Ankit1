<?php

namespace App\Repositories;

use App\Interfaces\DepartmentRepositoryInterface;
use App\Models\Department;

class DepartmentRepository implements DepartmentRepositoryInterface
{
    // get all department
    public function getAllData($data)
    {
        $queryData = Department::GetTextSearch($data);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );

        return $query;
    }

    // Store department
    public function store($data)
    {
        return Department::create($data);
    }

    // Update department
    public function update($departmentId, array $newDepartmentDetails)
    {
        return Department::find($departmentId)->update(
            $newDepartmentDetails
        );
    }

    // Delete department
    public function delete($departmentId)
    {
        return Department::destroy($departmentId);
    }

    public function edit($departmentId)
    {
        return Department::find($departmentId);
    }
}