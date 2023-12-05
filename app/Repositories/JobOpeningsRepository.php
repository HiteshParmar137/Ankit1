<?php

namespace App\Repositories;

use App\Interfaces\JobOpeningsRepositoryInterface;
use App\Models\JobOpenings;

class JobOpeningsRepository extends BaseRepository implements
    JobOpeningsRepositoryInterface
{
    // get all jobOpenings
    public function getAllData($data)
    {
        $queryData = jobOpenings::GetTextSearch($data);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );

        return $query;
    }

    // Store jobOpenings
    public function store($data)
    {
        return jobOpenings::create($data);
    }

    // editing the specified resource
    public function edit($jobOpeningsId)
    {
        return jobOpenings::find($jobOpeningsId);
    }

    // Update the specified resource
    public function update($jobOpeningsId, array $newJobOpeningsDetails)
    {
        return jobOpenings::find($jobOpeningsId)->update(
            $newJobOpeningsDetails
        );
    }

    // Delete jobOpenings
    public function delete($jobOpeningsId)
    {
        return jobOpenings::destroy($jobOpeningsId);
    }
}