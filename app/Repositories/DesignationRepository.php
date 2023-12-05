<?php

namespace App\Repositories;

use App\Interfaces\DesignationRepositoryInterface;
use App\Models\Designation;

class DesignationRepository extends BaseRepository implements
    DesignationRepositoryInterface
{
    // get all designation
    public function getAllData($data)
    {
        $queryData = Designation::GetTextSearch($data);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store designation
    public function store($data)
    {
        return Designation::create($data);
    }

    // editing the specified resource
    public function edit($designationId)
    {
        return Designation::find($designationId);
    }

    // Update the specified resource
    public function update($designationId, array $newDesignationDetails)
    {
        return Designation::find($designationId)->update(
            $newDesignationDetails
        );
    }

    // Delete designation
    public function delete($designationId)
    {
        return Designation::destroy($designationId);
    }
}