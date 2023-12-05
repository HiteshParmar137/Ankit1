<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectAssignRequest;
use App\Interfaces\ProjectAssigneeInterface;
use App\Models\Project;
use App\Models\ProjectAssignee;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ProjectAssigneeController extends Controller
{   
    public function __construct(protected ProjectAssigneeInterface $projectAssigneeRepository)
    {
        $this->projectAssigneeRepository = $projectAssigneeRepository;
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
                $projectAssign = $this->projectAssigneeRepository->getAllData($request->all());
                $view = \View::make('project-assign.partials.lists', [
                    'projectAssign' => $projectAssign,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $users = User::with('roles')->where('id','!=',Auth::id())
                ->whereHas('roles', function($query) {
                    $query->where('slug', '!=', User::SUPER_ADMIN_ROLE_SLUG);
                })
                ->Active()
                ->select('first_name', 'last_name' ,'id')
                ->get();
                $projectId = Crypt::decrypt($id);
                $project = Project::select('id', 'name')->find($projectId);
                $query_params = $request->all();
                $projectAssignUser = ProjectAssignee::with('user')->where('project_id', $project->id)->get();
                $taskTypes = TaskType::all();
                return view('project-assign.index', compact('users', 'project' ,'query_params', 'projectAssignUser', 'taskTypes'));
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
    public function store(ProjectAssignRequest $request)
    {   
        try {
            $startDate = dateFormate($request->start_date);
            $endDate = dateFormate($request->end_date);
            $data = [
                'project_id' => $request->project_id,
                'user_id'    => $request->user_id,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ];

            $this->projectAssigneeRepository->store($data);
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
     * @param  \App\Models\ProjectAssignee  $projectAssignee
     * @return \Illuminate\Http\Response
     */
    public function show(ProjectAssignee $projectAssignee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProjectAssignee  $projectAssignee
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $projectAssignId = Crypt::decrypt($id);
            $projectAssign = $this->projectAssigneeRepository->edit($projectAssignId);
            $users = User::with('roles')->where('id','!=',Auth::id())
                        ->whereHas('roles', function($query) {
                            $query->where('slug', '!=', User::SUPER_ADMIN_ROLE_SLUG);
                        })
                        ->Active()
                        ->select('first_name', 'last_name' ,'id')
                        ->get();
            $view = \View::make('modal.project-assign-form-edit-data', [
                'projectAssign' => $projectAssign,
                'users' => $users,
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
     * @param  \App\Models\ProjectAssignee  $projectAssign
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectAssignRequest $request, $id)
    {
        try {

            $projectAssignId = Crypt::decrypt($id);
            $startDate = dateFormate($request->start_date);
            $endDate = dateFormate($request->end_date);

            $data = [
                'project_id' => $request->project_id,
                'user_id'    => $request->user_id,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ];

            $this->projectAssigneeRepository->update($projectAssignId, $data);
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
     * @param  \App\Models\ProjectAssignee  $projectAssign
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $projectAssignId = Crypt::decrypt($id);
            $this->projectAssigneeRepository->delete($projectAssignId);
            return response()->json(['message' => 'Data delete successfully.', 'success' => true], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }
}
