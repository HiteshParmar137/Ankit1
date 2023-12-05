<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfileRequest;
use App\Http\Resources\Api\V1\Profile\ProfileDetailsResource;
use App\Http\Resources\Api\V1\Profile\ProfileListResource;
use App\Models\Profile;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProfileRequest $request): object
    {
        try {
            $perPage = $request->per_page_records ?? 10;
            $page = $page ?? 1;

            $profiles = Profile::select(
                    'id',
                    'profile_code',
                    'default_hours',
                    'users',
                    'description',
                )
                ->filter($request->all())
                ->paginate($perPage);

            $profileListResource = ProfileListResource::collection($profiles);
            
            return $this->paginatedSuccessResponse(200, "Profiles", $profileListResource);

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
    public function store(ProfileRequest $request): object
    {
        try {

            DB::beginTransaction();

            $profile = Profile::create([
                'default_hours' => $request->default_hours,
                'users' => json_encode($request->users),
                'description' => $request->description
            ]);
            
            DB::commit();

            return $this->successResponse(200, "Profile added successfully", ['id' => $profile->id]);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error($e);
                return $this->errorResponse(500, "Something is wrong");
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $profileId
     * @return \Illuminate\Http\Response
     */
    public function show(int $profileId): object
    {
        try {
            $profile = Profile::select(
                    'id',
                    'default_hours',
                    'users',
                    'description',                
                )
                ->find($profileId);

            if (!empty($profile)) {
                $profileDetailsResource = new ProfileDetailsResource($profile);
                return $this->successResponse(200, "Profile details", $profileDetailsResource);
            } else {
                return $this->errorResponse(400, "No profile found");
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
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function update(ProfileRequest $request): object
    {
        try {
            $profileId = $request->profile_id ?? null;

            $profile = Profile::select(
                    'id',
                    'default_hours',
                    'users',
                    'description',                    
                )
                ->find($profileId);

            if (!empty($profile)) {
                DB::beginTransaction();

                tap($profile)->update([
                    'default_hours' => $request->default_hours,
                    'users' => json_encode($request->users),
                    'description' => $request->description,
                ]);
    
                DB::commit();

                return $this->successResponse(200, "Profile updated", ['id' => $profile->id]);

            } else {
                return $this->errorResponse(400, "No profile found");
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
     * @param  int  $profileId
     * @return \Illuminate\Http\Response
     */
    public function destroy($profileId): object
    {
        try {
            $profile = Profile::find($profileId);
            
            if (!empty($profile)) {
                DB::beginTransaction();

                $profile->delete();

                DB::commit();

                return $this->successResponse(200, "Profile deleted successfully", ['id' => $profileId]);
                
            } else {
                return $this->errorResponse(400, "No profile found");
            }

        } catch (Exception $e) {
            DB::rollBack();          
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }
}
