<?php

namespace App\Repositories;

use App\Interfaces\FreelancerInterface;
use App\Models\Freelancer;
use App\Models\FreelancerSkill;

class FreelancerRepository extends BaseRepository implements FreelancerInterface
{
    // get all Freelancer
    public function getAllData($data)
    {
        $queryData = Freelancer::GetTextSearch($data)->orderBy($data['sort_column'], $data['sort_type']);
        $query = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $query;
    }

    // Store Freelancer
    public function store(Array $data)
    {   
        $freelancer = Freelancer::create($data);

        foreach($data['technologies'] as $technology){
            $punchLogTimes = new FreelancerSkill();    
            $punchLogTimes->freelancer_id = $freelancer->id;
            $punchLogTimes->technology_id = $technology;
            $punchLogTimes->save();            
        }

        return true;
    }

    // editing the specified resource
    public function edit($freelancerId)
    {
        return Freelancer::with('technologies')->find($freelancerId);
    }

    // Update the specified resource
    public function update($freelancerId, array $newResourceRequestDetails)
    {   
        $freelancer = tap(Freelancer::find($freelancerId))->update($newResourceRequestDetails);

        FreelancerSkill::where('freelancer_id',$freelancerId)->delete();
        
        foreach($newResourceRequestDetails['technologies'] as $technology){
            $projectTechnology = new FreelancerSkill();    
            $projectTechnology->freelancer_id = $freelancer->id;
            $projectTechnology->technology_id = $technology;
            $projectTechnology->save();            
        }

        return true;
    }

    // Delete Resource Request
    public function delete($freelancerId)
    {
        return Freelancer::destroy($freelancerId);
    }
}