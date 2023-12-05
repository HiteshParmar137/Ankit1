<?php

namespace App\Repositories;

use App\Interfaces\ProjectAssigneeInterface;
use App\Models\ProjectAssignee;

class ProjectAssigneeRepository extends BaseRepository implements ProjectAssigneeInterface
{
    // get all Created user leave
    public function getAllData($data)
    {   
        $queryData = ProjectAssignee::with('user')->where('project_id', $data['project_id'])->orderBy('id', 'desc');
        $leaves = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $leaves;
    }

    // Store Leave data
    public function store($data)
    {   
        ProjectAssignee::create($data);
        return true;
    }

    // editing the specified resource
    public function edit($projectAssignId)
    {
        return ProjectAssignee::with('user')->find($projectAssignId);
    }

    // Update the specified resource
    public function update($projectAssignId, array $newProjectAssignDetails)
    {
        $project = tap(ProjectAssignee::find($projectAssignId))->update($newProjectAssignDetails);
        
    
        return true;
    }

    // Delete specified resource
    public function delete($projectAssignId)
    {   
        ProjectAssignee::destroy($projectAssignId);
        return true; 
    }
}