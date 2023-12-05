<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectTaskRequest;
use App\Interfaces\ProjectTaskInterface;
use App\Models\Project;
use App\Models\ProjectAssignee;
use App\Models\ProjectTask;
use App\Models\TaskType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ProjectTaskController extends Controller
{   
    public function __construct(protected ProjectTaskInterface $projectTaskRepository)
    {
        $this->projectTaskRepository = $projectTaskRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        try {
            if ($request->ajax()) {
                $request->merge(['project_id' => $id]);
                $projectTask = $this->projectTaskRepository->getAllData($request->all());
                $view = \View::make('project-task.partials.lists', [
                    'projectTask' => $projectTask,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $projectId = Crypt::decrypt($id);
                $project = Project::select('id', 'name')->find($projectId);
                $projectAssignUser = ProjectAssignee::with('user')->where('project_id', $project->id)->get();
                $taskTypes = TaskType::all();
                if ($projectAssignUser->count() > 0 ) {
                    $query_params = $request->all();
                    return view('project-task.index', compact('projectAssignUser', 'taskTypes' ,'project' ,'query_params'));
                } else {
                    return redirect()->route('project-assign-lists', Crypt::encrypt($project->id))->with('error', 'Add Assignees to your Project First!');
                }
            }
        } catch (\Exception $e) {
            \Log::error($e);
            if ($request->ajax()) {
                return response()->json(
                    [
                        'message' => 'Something is wrong',
                        'success' => false,
                        'error_msg' => $e->getMessage(),
                    ],
                    500
                );
            } else {
                return redirect()
                    ->back()
                    ->with([
                        'error' => 'Something is wrong',
                        'error_msg' => $e->getMessage(),
                    ]);
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProjectTaskRequest $request)
    {
        try {
            $startDate = dateFormate($request->start_date);
            $endDate = dateFormate($request->end_date);
            $data = [
                'short_name'    => shortNameGenerate(),
                'project_id'  => $request->project_id,
                'user_id'     => $request->user_id,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'hours'       => $request->hours,
                'type'        => $request->type,
                'title'       => $request->title,
                'priority'    => $request->priority,
                'status'      => $request->status,
                'billable'    => $request->billable,
                'description' => $request->description,
            ];

            $this->projectTaskRepository->store($data);
            return response()->json(
                ['message' => 'Data store successfully.', 'success' => true],
                200
            );
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Something is wrong',
                    'success' => false,
                    'error_msg' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tasks  $tasks
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $shortName)
    {   
        try {
            if ($request->ajax()) {
                $userId = Auth::id();
                $myTask = ProjectTask::where('short_name', $shortName)->first();
                
                if (($myTask->user_id != $userId) && (!auth()->user()->isAdmin())) {
                    return response()->json([
                        'html' => '',
                        'message' => 'You are not permitted to access this page.',
                        'success' => false,
                    ]);
                } else {
                    $task = $this->projectTaskRepository->show($shortName);
                    $view = \View::make('project-task.partials.details_data', [
                        'task' => $task,
                    ]);
                    $html = $view->render();
                    return response()->json([
                        'html' => $html,
                        'message' => 'Data Fetched Successfully!',
                        'success' => true,
                    ]);
                }
            } else {
                $tasksShortName = $shortName;
                return view('project-task.partials.details', compact('tasksShortName'));
            }
        } catch (\Exception $e) {
            \Log::error($e);
            if ($request->ajax()) {
                return response()->json(
                    [
                        'message' => 'Something is wrong',
                        'success' => false,
                        'error_msg' => $e->getMessage(),
                    ],
                    500
                );
            } else {
                return redirect()
                    ->back()
                    ->with([
                        'error' => 'Something is wrong',
                        'error_msg' => $e->getMessage(),
                    ]);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tasks  $tasks
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $project)
    {
        try {
            $taskId = Crypt::decrypt($id);
            $projectId = Crypt::decrypt($project);
            $projectAssignUser = ProjectAssignee::with('user')->where('project_id', $projectId)->get();
            $taskTypes = TaskType::all();
            $task = $this->projectTaskRepository->edit($taskId);
            $view = \View::make('modal.project-task-form-edit-data', [
                'project' => $task,
                'projectAssignUser' => $projectAssignUser,
                'taskTypes' => $taskTypes,
                'task' => $task,
            ]);
            $html = $view->render();
            return response()->json([
                'html' => $html,
                'message' => 'Data Fetched Successfully!',
                'success' => true,
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Something is wrong',
                    'success' => false,
                    'error_msg' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tasks  $tasks
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectTaskRequest $request, $id)
    {
        try {
            $projectTaskId = Crypt::decrypt($id);
            $startDate = dateFormate($request->start_date);
            $endDate = dateFormate($request->end_date);
            $data = [
                'project_id'  => $request->project_id,
                'user_id'     => $request->user_id,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'hours'       => $request->hours,
                'type'        => $request->type,
                'title'       => $request->title,
                'priority'    => $request->priority,
                'status'      => $request->status,
                'billable'    => $request->billable,
                'description' => $request->description,
            ];
            $this->projectTaskRepository->update($projectTaskId, $data);
            return response()->json(
                ['message' => 'Data store successfully.', 'success' => true],
                200
            );
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(
                [
                    'message' => 'Something is wrong',
                    'success' => false,
                    'error_msg' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tasks  $tasks
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $projectTaskId = Crypt::decrypt($id);
            $this->projectTaskRepository->delete($projectTaskId);
            return response()->json(['message' => 'Data delete successfully.', 'success' => true], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }
}
