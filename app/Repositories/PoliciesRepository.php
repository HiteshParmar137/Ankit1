<?php

namespace App\Repositories;

use App\Interfaces\HolidayRepositoryInterface;
use App\Interfaces\PoliciesRepositoryInterface;
use App\Jobs\PolicyJob;
use App\Models\Holiday;
use App\Models\Policies;
use App\Models\User;

class PoliciesRepository extends BaseRepository implements PoliciesRepositoryInterface
{
    // get all Monthly Events. 
    public function getAllData($data)
    {
        $queryData = Policies::GetTextSearch($data);
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
        $policy    = Policies::create($data);
        $userEmail = User::get()->pluck('email')->toArray();
        $title     = 'Added';
        PolicyJob::dispatch($policy, $userEmail, $title);
        return $policy;
    }

    // editing the specified resource
    public function edit($policyId)
    {
        return Policies::find($policyId);
    }

    // Update the specified resource
    public function update($policyId, array $newPolicyIdDetails)
    {
        $policy    = Policies::find($policyId)->update($newPolicyIdDetails);
        $userEmail = User::get()->pluck('email')->toArray();
        $title     = 'update';
        PolicyJob::dispatch($policy, $userEmail, $title);
        return $policy;
    }

    // Delete specified resource
    public function delete($policyId)
    {
        return Policies::destroy($policyId);
    }

    public function show($policyId)
    {
        return Policies::find($policyId);
    }
}
