<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProjectRequest;
use App\Http\Resources\Api\V1\Project\ProjectListResource;
use App\Models\Project;
use Exception;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProjectRequest $request): object
    {
        return $this->errorResponse(503, "Service Unavailable");

        try {
            $perPage = $request->per_page_records ?? 10;
            $page = $page ?? 1;

            $projects = Project::select('id', 'name', 'status')
                ->filter($request->all())
                ->paginate($perPage);

            $projectsResource = ProjectListResource::collection($projects);
            
            return $this->paginatedSuccessResponse(200, "Projects", $projectsResource);

        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }
}
