<?php

namespace App\Repositories;

use App\Interfaces\TechnologyRepositoryInterface;
use App\Models\Technology;

class TechnologyRepository extends BaseRepository implements TechnologyRepositoryInterface
{
    // get all Monthly Events.
    public function getAllData($data)
    {
        $queryData = Technology::GetTextSearch($data);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store Monthly Events
    public function store($data)
    {
        $technology = Technology::create($data);
        return $technology;
    }

    // editing the specified resource
    public function edit($technologyId)
    {
        return Technology::find($technologyId);
    }

    // Update the specified resource
    public function update($technologyId, array $newTechnologyDetails)
    {
        return Technology::find($technologyId)->update($newTechnologyDetails);
    }

    // Delete specified resource
    public function delete($technologyId)
    {
        return Technology::destroy($technologyId);
    }
}