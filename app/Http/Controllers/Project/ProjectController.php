<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Http\Requests\TechnologyRequest;
use App\Interfaces\ProjectInterface;
use App\Interfaces\TechnologyRepositoryInterface;
use App\Models\Clients;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ProjectController extends Controller
{   
    public function __construct(protected ProjectInterface $projectRepository, protected TechnologyRepositoryInterface $technologyRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->technologyRepository = $technologyRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $projects = $this->projectRepository->getAllData($request->all());
                $view = \View::make('project.partials.lists', [
                    'projects' => $projects,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $clients = Clients::all()
                        ->pluck('name', 'id')
                        ->toArray();
                $technologies = Technology::all()
                        ->pluck('name', 'id')
                        ->toArray();
                $query_params = $request->all();
                return view('project.index', compact('clients', 'technologies', 'query_params'));
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
    public function store(ProjectRequest $request)
    {   
        try {
            $this->projectRepository->store($request->all());
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
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            if ($request->ajax()) {
                $projectId = decrypt($id);
                $project = $this->projectRepository->show($projectId);
                $view = \View::make('project.partials.details_data', [
                    'project' => $project,
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $projectId = $id;
                return view('project.partials.details', compact('projectId'));
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
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $projectId = decrypt($id);
            $project = $this->projectRepository->edit($projectId);
            $clients = Clients::all()->pluck('name', 'id')->toArray();
            $technologies = Technology::all()->pluck('name', 'id')->toArray();
            $view = \View::make('modal.project-form-edit-data', [
                'project' => $project,
                'clients' => $clients,
                'technologies' => $technologies,
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
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(ProjectRequest $request, $id)
    {
        try {
            $projectId = decrypt($id);
            $this->projectRepository->update($projectId, $request->all());
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
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $projectId = decrypt($id);
            $this->projectRepository->delete($projectId);
            return response()->json(['message' => 'Data delete successfully.', 'success' => true], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Something is wrong', 'success' => false, 'error_msg' => $e->getMessage()], 500);
        }
    }

    public function projectTechnologyStore(TechnologyRequest $request)
    {
        try {
            $technology = $this->technologyRepository->store($request->all());
            $technology_id = $technology->id;
            $technology_name = $technology->name;
            return response()->json([
                'success' => "New technology added successfully.",
                'id' => $technology_id,
                'name' => $technology_name
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

    public function projectClientStore(Request $request)
    {
        try {
            $client = new Clients();
            $client->name = $request->name;
            $client->save();
            $client_id = $client->id;
            $client_name = $client->name;
            return response()->json([
                'success' => "New Client added Successfully.",
                'id' => $client_id,
                'name' => $client_name
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

    public function myProjects(Request $request) 
    {
        try {
            if ($request->ajax()) {
                $projects = $this->projectRepository->myProjectData($request->all());
                $view = \View::make('project.partials.my-project-lists', [
                    'projects' => $projects,
                    'return_back_handle' => http_build_query($request->all()),
                ]);
                $html = $view->render();
                return response()->json([
                    'html' => $html,
                    'message' => 'Data Fetched Successfully!',
                    'success' => true,
                ]);
            } else {
                $query_params = $request->all();
                return view('project.my-project', $query_params);
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
}
