<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterRequest;
use App\Http\Resources\Api\V1\Holiday\HolidayOptionsResource;
use App\Http\Resources\Api\V1\Profile\ProfileOptionsResource;
use App\Http\Resources\Api\V1\Project\ProjectOptionsResource;
use App\Http\Resources\Api\V1\User\UserOptionsResource;
use App\Models\Holiday;
use App\Models\Profile;
use App\Models\Project;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class MasterController extends Controller
{
    public function getProjects(MasterRequest $request): object
    {
        return $this->errorResponse(503, "Service Unavailable");

        try {
            $projects = Project::select('id', 'name', 'status')
                ->filter($request->all())
                ->get();

            $projectsResource = ProjectOptionsResource::collection($projects);
            
            return $this->successResponse(200, "Projects", $projectsResource);

        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    public function getProfiles(): object
    {
        try {
            $profiles = Profile::select(
                    'id',
                    'profile_code',
                    'default_hours',
                )
                ->get();

            $profileOptionsResource = ProfileOptionsResource::collection($profiles);
            
            return $this->successResponse(200, "Profiles", $profileOptionsResource);

        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    public function getHolidays(MasterRequest $request): object
    {
        try {
            $holidays = Holiday::select('id', 'name', 'date', 'status')
                ->filter($request->all())
                ->get();

            $holidayOptionsResource = HolidayOptionsResource::collection($holidays);
            
            return $this->successResponse(200, "Holidays", $holidayOptionsResource);

        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    public function getUsers(MasterRequest $request): object
    {
        try {
            $users = User::select(
                'id',
                'first_name',
                'last_name',
                'status'
            )
            ->usersNamedRole($request->role)
            ->with('userProfile.profile')
            ->orderBy('id', 'desc')
            ->get();

            $usersResource = UserOptionsResource::collection($users);
            
            return $this->successResponse(200, "Users", $usersResource);

        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }
}
