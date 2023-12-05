<?php

namespace App\Repositories;

use App\Interfaces\ProjectInterface;
use App\Jobs\LeaveJob;
use App\Models\Leaves;
use App\Models\Project;
use App\Models\ProjectTechnology;
use Illuminate\Support\Facades\Auth;

class ProjectRepository extends BaseRepository implements ProjectInterface
{
    // get all Created user leave
    public function getAllData($data)
    {
        $queryData = Project::GetTextSearch($data)->orderBy($data['sort_column'], $data['sort_type']);
        $project = $queryData->paginate(
            $data['record_per_page'] == 'all'
                ? $queryData->count()
                : $data['record_per_page']
        );
        return $project;
    }

    // Store Leave data
    public function store($data)
    {   
        $project = New Project();
        $project->short_name = shortNameGenerate();
        $project->name = $data['name'];
        $project->type = $data['type'];
        $project->start_date = dateFormate($data['start_date']);
        $project->end_date = dateFormate($data['end_date']);
        $project->client_id = $data['client_id'];
        $project->status = $data['status'];
        $project->description = $data['description'];
        $project->cost = $data['cost'];
        $project->cost_type = $data['cost_type'];
        $project->hours = $data['hours'] ?? null;
        $project->save();

        foreach($data['technologies'] as $technology){
            $punchLogTimes = new ProjectTechnology();    
            $punchLogTimes->project_id = $project->id;
            $punchLogTimes->technology_id = $technology;
            $punchLogTimes->save();            
        }

        return true;
    }

    // editing the specified resource
    public function edit($projectId)
    {
        return Project::with('technologies')->find($projectId);
    }

    // Update the specified resource
    public function update($projectId, array $newProjectDetails)
    {
        $project = tap(Project::find($projectId))->update($newProjectDetails);
        
        ProjectTechnology::where('project_id',$projectId)->delete();
        
        foreach($newProjectDetails['technologies'] as $technology){
            $projectTechnology = new ProjectTechnology();    
            $projectTechnology->project_id = $project->id;
            $projectTechnology->technology_id = $technology;
            $projectTechnology->save();            
        }

        return true;
    }

    // Delete specified resource
    public function delete($ProjectId)
    {   
        Project::destroy($ProjectId);
        return true; 
    }

    // get assigned project
    public function myProjectData($data) 
    {   
        $queryData = Project::with(['assignee' => function($query) { 
            $query->where('user_id', Auth::id()); }, 
            'tasks' => function($query) { 
                $query->where('user_id', Auth::id()); },
            'technologies', 'technologies.technologyData'])
            ->orderBy('id', 'desc');

        if($data['status'] != 'all') {
            $queryData->where('status', $data['status']);
        }
        $myProject = $queryData->paginate(
            $data['record_per_page'] == 'all'
            ? $queryData->count()
            : $data['record_per_page']
        );

        // dd($myProject->toArray());
        return $myProject;
    }

    // show the specified resource
    public function show($projectId)
    {
        return Project::with(['client', 'technologies', 'technologies.technologyData' ,'assignee', 'assignee.user'])->find($projectId);
    }
}