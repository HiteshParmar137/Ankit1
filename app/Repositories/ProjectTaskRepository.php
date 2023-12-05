<?php

namespace App\Repositories;

use App\Interfaces\ProjectTaskInterface;
use App\Models\ProjectTask;

class ProjectTaskRepository extends BaseRepository implements ProjectTaskInterface
{
    // get all Created user leave
    public function getAllData($data)
    {   
        $queryData = ProjectTask::with(['user', 'taskType'])
            ->GetTextSearch($data)
            ->where('project_id', $data['project_id'])
            ->orderBy($data['sort_column'], $data['sort_type']);
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
        ProjectTask::create($data);
        return true;
    }

    // editing the specified resource
    public function edit($projectTaskId)
    {
        return ProjectTask::with(['user', 'taskType'])->find($projectTaskId);
    }

    // editing the specified resource
    public function show($shortName)
    {
        return ProjectTask::with(['project:id,name', 'user', 'taskType'])->where('short_name', $shortName)->first();
    }

    // Update the specified resource
    public function update($projectTaskId, array $newProjectTaskDetails)
    {   
        tap(ProjectTask::find($projectTaskId))->update($newProjectTaskDetails);
        return true;
    }

    // Delete specified resource
    public function delete($projectTaskId)
    {   
        ProjectTask::destroy($projectTaskId);
        return true; 
    }
}