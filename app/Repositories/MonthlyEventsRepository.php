<?php

namespace App\Repositories;

use App\Interfaces\MonthlyEventsRepositoryInterface;
use App\Models\MonthlyEvent;

class MonthlyEventsRepository extends BaseRepository implements
    MonthlyEventsRepositoryInterface
{
    // get all Monthly Events.
    public function getAllData($data)
    {
        $queryData = MonthlyEvent::GetTextSearch($data);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store a newly created Monthly Events in storage.
    public function store($data)
    {
        return MonthlyEvent::create($data);
    }

    // editing the specified resource
    public function edit($monthlyEventId)
    {
        return MonthlyEvent::find($monthlyEventId);
    }

    // Update the specified resource
    public function update($monthlyEventId, array $newJobOpeningsDetails)
    {
        return MonthlyEvent::find($monthlyEventId)->update(
            $newJobOpeningsDetails
        );
    }

    // Delete specified resource
    public function delete($monthlyEventId)
    {
        return MonthlyEvent::destroy($monthlyEventId);
    }
}