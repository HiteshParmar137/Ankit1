<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorklogRequest;
use App\Http\Resources\Api\V1\Worklog\WorklogDetailResource;
use App\Http\Resources\Api\V1\Worklog\WorklogListResource;
use App\Models\Worklog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorklogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(WorklogRequest $request): object
    {
        return $this->errorResponse(503, "Service Unavailable");

        try {
            $perPage = $request->per_page_records ?? 10;
            $page = $page ?? 1;

            $user = Helpers::getLoginUser();

            $worklogs = Worklog::select(
                    'worklogs.id as id',
                    'worklogs.user_id as user_id',
                    'worklogs.date as date',
                    'worklogs.worked_hours as worked_hours',
                    'worklogs.description as description',
                    'projects.id as project_id',
                    'projects.name as project_name',
                )
                ->join('projects', 'projects.id', '=', 'worklogs.project_id')
                ->onlyForThisUser($user->id)
                ->filter($request->all())
                ->paginate($perPage);

            $worklogResource = WorklogListResource::collection($worklogs);
            
            return $this->paginatedSuccessResponse(200, "Worklogs", $worklogResource);

        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WorklogRequest $request): object
    {
        return $this->errorResponse(503, "Service Unavailable");

        try {
            $user = Helpers::getLoginUser();

            $request->merge([
                'user_id' => $user->id,
            ]);

            DB::beginTransaction();

            $worklog = Worklog::create(
                $request->only([
                    'user_id',
                    'project_id',
                    'date',
                    'worked_hours',
                    'description',
                ])
            );

            DB::commit();

            return $this->successResponse(200, "Worklog added successfully", ['id' => $worklog->id]);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error($e);
                return $this->errorResponse(500, "Something is wrong");
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $worklogId
     * @return \Illuminate\Http\Response
     */
    public function show(int $worklogId): object
    {
        return $this->errorResponse(503, "Service Unavailable");

        try {
            $user = Helpers::getLoginUser();

            $worklog = Worklog::select(
                    'id',
                    'user_id',
                    'project_id',
                    'date',
                    'worked_hours',
                    'description',
                )
                ->whereUserId($user->id)
                ->whereId($worklogId)
                ->first();

            if (!empty($worklog)) {
                $worklogDetailResource = new WorklogDetailResource($worklog);

                return $this->successResponse(200, "Worklog details", $worklogDetailResource);

            } else {
                return $this->errorResponse(400, "No worklog found");
            }
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $worklogId
     * @return \Illuminate\Http\Response
     */
    public function update(WorklogRequest $request): object
    {
        return $this->errorResponse(503, "Service Unavailable");

        try {
            $worklogId = $request->worklog_id ?? null;
            $user = Helpers::getLoginUser();

            $worklog = Worklog::select(
                    'id',
                    'user_id',
                    'project_id',
                    'date',
                    'worked_hours',
                    'description',
                )
                ->whereUserId($user->id)
                ->whereId($worklogId)
                ->first();

            if (!empty($worklog)) {
                DB::beginTransaction();

                tap($worklog)->update(
                    $request->only([
                        'project_id', 'date', 'worked_hours', 'description'
                    ])
                );
    
                DB::commit();
    
                return $this->successResponse(200, "Worklog updated successfully", ['id' => $worklog->id]);

            } else {
                return $this->errorResponse(400, "No worklog found");
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $worklogId
     * @return \Illuminate\Http\Response
     */
    public function destroy($worklogId): object
    {
        return $this->errorResponse(503, "Service Unavailable");

        try {
            $user = Helpers::getLoginUser();

            $worklog = Worklog::whereUserId($user->id)
                ->whereId($worklogId)
                ->first();
            
            if (!empty($worklog)) {
                DB::beginTransaction();

                $worklog->delete();

                DB::commit();

                return $this->successResponse(200, "Worklog deleted successfully", ['id' => $worklogId]);
                
            } else {
                return $this->errorResponse(400, "No worklog found");
            }

        } catch (Exception $e) {
            DB::rollBack();          
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }
}
