<?php

namespace App\Repositories;

use App\Interfaces\HolidayRepositoryInterface;
use App\Models\Holiday;

class HolidayRepository extends BaseRepository implements
    HolidayRepositoryInterface
{
    // get all Monthly Events.
    public function getAllData($data)
    {
        $queryData = Holiday::GetTextSearch($data);
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
        return Holiday::create($data);
    }

    // editing the specified resource
    public function edit($holidayId)
    {
        return Holiday::find($holidayId);
    }

    // Update the specified resource
    public function update($holidayId, array $newholidayDetails)
    {
        return Holiday::find($holidayId)->update($newholidayDetails);
    }

    // Delete specified resource
    public function delete($holidayId)
    {
        return Holiday::destroy($holidayId);
    }
}