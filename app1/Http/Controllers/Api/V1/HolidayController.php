<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\HolidayRequest;
use App\Http\Resources\Api\V1\Holiday\HolidayDetailsResource;
use App\Http\Resources\Api\V1\Holiday\HolidayListResource;
use App\Models\Holiday;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(HolidayRequest $request): object
    {
        try {
            $perPage = $request->per_page_records ?? 10;
            $page = $page ?? 1;

            $holidays = Holiday::select('id', 'name', 'date', 'description', 'status')
                ->filter($request->all())
                ->paginate($perPage);

            $holidaysResource = HolidayListResource::collection($holidays);
            
            return $this->paginatedSuccessResponse(200, "Holidays", $holidaysResource);

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
    public function store(HolidayRequest $request): object
    {
        try {

            DB::beginTransaction();

            $holiday = Holiday::create(
                $request->only([
                    'name',
                    'date',
                    'description',
                    'status'
                ])
            );
            
            DB::commit();

            return $this->successResponse(200, "Holiday added successfully", ['id' => $holiday->id]);

            } catch (Exception $e) {
                DB::rollBack();
                Log::error($e);
                return $this->errorResponse(500, "Something is wrong");
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $holidayId
     * @return \Illuminate\Http\Response
     */
    public function show(int $holidayId): object
    {
        try {
            $holiday = Holiday::select(
                'id',
                'name',
                'date',
                'description',
                'status'               
            )
            ->find($holidayId);

            if (!empty($holiday)) {
                $holidayDetailsResource = new HolidayDetailsResource($holiday);

                return $this->successResponse(200, "Holiday details", $holidayDetailsResource);

            } else {
                return $this->errorResponse(400, "No holiday found");
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
     * @return \Illuminate\Http\Response
     */
    public function update(HolidayRequest $request): object
    {
        try {
            $holidayId = $request->holiday_id ?? null;

            $holiday = Holiday::select(
                    'id',
                    'name',
                    'date',
                    'description',
                    'status'                 
                )
                ->find($holidayId);

            if (!empty($holiday)) {
                DB::beginTransaction();

                tap($holiday)->update(
                    $request->only([
                        'name',
                        'date',
                        'description',
                        'status'
                    ])
                );
    
                DB::commit();
    
                return $this->successResponse(200, "Holiday updated successfully", ['id' => $holiday->id]);

            } else {
                return $this->errorResponse(400, "No Holiday found");
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
     * @param  int  $holidayId
     * @return \Illuminate\Http\Response
     */
    public function destroy($holidayId): object
    {
        try {
            $holiday = Holiday::find($holidayId);
            
            if (!empty($holiday)) {
                DB::beginTransaction();

                $holiday->delete();

                DB::commit();

                return $this->successResponse(200, "Holiday deleted successfully", ['id' => $holidayId]);
                
            } else {
                return $this->errorResponse(400, "No holiday found");
            }

        } catch (Exception $e) {
            DB::rollBack();          
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }
}
