<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\ManageTimesheetLogsExports;
use App\Helper\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ManageTimesheetRequest;
use App\Http\Resources\Api\V1\ManageTimesheet\ManageTimesheetListResource;
use App\Http\Resources\Api\V1\ManageTimesheet\ManageTimesheetProfileDetailsResource;
use App\Http\Resources\Api\V1\ManageTimesheet\ManageTimesheetResource;
use App\Jobs\SendManageTimesheetLogsMailJob;
use App\Models\Holiday;
use App\Models\Timesheet;
use App\Models\TimesheetLog;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ManageTimesheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ManageTimesheetRequest $request): object
    {
        try {
            $user = User::find($request->user_id);

            if (empty($user)) {
                return $this->errorResponse(400, "User not found");
            }

            $manageTimesheet = Timesheet::with([
                    'logs:id,timesheet_id,date,worked_hours,is_holiday_or_on_leave'
                ])
                ->select(
                    'id',
                    'user_id',
                    'start_date',
                    'end_date',
                    'description',
                    'updated_at'
                )
                ->onlyForThisUser($user->id)
                ->filter($request->all())
                ->first();

            if (!empty($manageTimesheet)) {
                $manageTimesheetResource = new ManageTimesheetResource($manageTimesheet);
                
                return $this->successResponse(200, "Timesheet with logs details", $manageTimesheetResource);
            } else {
                $userProfile = $user->userProfile->profile;
                $profileDetailsResource['profile'] = new ManageTimesheetProfileDetailsResource($userProfile);

                return $this->successResponse(200, "User profile details", $profileDetailsResource);
            }


        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function history(ManageTimesheetRequest $request): object
    {
        try {
            $perPage = $request->per_page_records ?? 10;
            $page = $page ?? 1;

            $user = User::find($request->user_id);

            if (empty($user)) {
                return $this->errorResponse(400, "User not found");
            }

            $manageTimesheets = Timesheet::with([
                    'logs:id,timesheet_id,worked_hours'
                ])
                ->select(
                    'id',
                    'user_id',
                    'start_date',
                    'end_date',
                    'description',
                )
                ->onlyForThisUser($user->id)
                ->filterHistory($request->all())
                ->paginate($perPage);

            $manageTimesheetListResource = ManageTimesheetListResource::collection($manageTimesheets);
            
            return $this->paginatedSuccessResponse(200, "Timesheets", $manageTimesheetListResource);

        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\ManageTimesheetRequest  $request
     * @return \Illuminate\Http\Response
     */ 
    public function update(ManageTimesheetRequest $request): object
    {
        try {
            $user = User::find($request->user_id);

            if (empty($user)) {
                return $this->errorResponse(400, "User not found");
            }

            $logs = collect($request->timesheet_logs);

            DB::beginTransaction();

            $timesheet = Timesheet::updateOrCreate([
                'user_id' => $user->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ], [
                'description' => $request->description,
                'updated_at' => now()
            ]);

            

            foreach ($logs as $log) {
                $holidays = Holiday::pluck('date')->toArray();
                $weekEndDays = Helpers::getWeekEndDays();

                /**
                 * Date: 10th May 2023
                 * 
                 * ! NOTE: Commented due to client suggestion as they want that user can also manage
                 * is_holiday_or_on_leave for the weekend days.
                 * 
                 * 
                 * if (
                 *     in_array(
                 *         Carbon::parse($log['date'])->format('Y-m-d'),
                 *         $holidays
                 *     ) ||
                 *     in_array(
                 *         Carbon::createFromFormat('Y-m-d', $log['date'])->format('l'),
                 *         $weekEndDays
                 *     )
                 * ) {
                 *     $log['is_holiday_or_on_leave'] = 1;
                 * }
                */

                /**
                 * Date: 6th June 2023
                 * 
                 * Due to client confirmation for the decs while export time-sheet it is
                 * compulsory to manage "is_holiday_or_on_leave" while add/update the logs
                 * 
                 * That's why added below condition to check for the holiday
                 */

                if (
                    in_array(
                        Carbon::parse($log['date'])->format('Y-m-d'),
                        $holidays
                    )
                ) {
                    $log['is_holiday_or_on_leave'] = 1;
                }

                TimesheetLog::updateOrCreate([
                    'user_id' => $user->id,
                    'timesheet_id' => $timesheet->id,
                    'date' => $log['date']
                ], [
                    'worked_hours' => $log['worked_hours'],
                    'is_holiday_or_on_leave' => $log['is_holiday_or_on_leave'],
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return $this->successResponse(200, "Timesheet updated successfully", ['id' => $timesheet->id]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Send the email of the timesheet.
     *
     * @param  \Illuminate\Http\ManageTimesheetRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function send(ManageTimesheetRequest $request): object
    {
        try {
            $user = User::find($request->user_id);

            if (empty($user)) {
                return $this->errorResponse(400, "User not found");
            }

            // $userProfile = $user->userProfile->profile;

            // $userProfileEmails = collect($userProfile->users)->pluck('email');

            // $emails = $userProfileEmails->merge($request->emails)->unique();

            /**
             * Date: 8th June: passed only incoming emails instead of taking user profile emails & request emails
             */
            
            $emails = collect($request->emails)->unique();

            $manageTimesheetLogs = TimesheetLog::with([
                    'timesheet:id,start_date,end_date,description'
                ])
                ->select(
                    'id',
                    'timesheet_id',
                    'date',
                    'worked_hours',
                    'is_holiday_or_on_leave'
                )
                ->onlyForThisYear()
                ->onlyForThisUser($user->id)
                ->orderBy('date')
                ->get();

            $fileName = $user->full_name . ' Quadtec Solutions Timesheet - Week Ending ' . Str::replace('-', '_', $manageTimesheetLogs->last()->date) . '.xlsx';

            $subject = $user->full_name . ' Timesheet ' . $manageTimesheetLogs->first()->date . '-' . $manageTimesheetLogs->last()->date;

            SendManageTimesheetLogsMailJob::dispatch($emails, $subject, $manageTimesheetLogs, $fileName, $user)->delay(now()->addSeconds(2));

            return $this->successResponse(200, "Timesheet sent successfully");
        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    /**
     * Export timesheet.
     *
     * @param  \Illuminate\Http\ManageTimesheetRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function export(ManageTimesheetRequest $request, int $timesheetId)
    {
        try {
            $user = User::find($request->user_id);

            if (empty($user)) {
                return $this->errorResponse(400, "User not found");
            }

            $manageTimesheetLogs = TimesheetLog::with([
                    'timesheet:id,start_date,end_date,description'
                ])
                ->select(
                    'id',
                    'timesheet_id',
                    'date',
                    'worked_hours',
                    'is_holiday_or_on_leave'
                )
                ->onlyForThisTimesheet($timesheetId)
                ->onlyForThisUser($user->id)
                ->orderBy('date')
                ->get();

            if ($manageTimesheetLogs->count()) {
                $fileName = $user->full_name . ' Quadtec Solutions Timesheet - Week Ending ' . Str::replace('-', '_', $manageTimesheetLogs->last()->date) . '.xlsx';
                return Excel::download(new ManageTimesheetLogsExports($manageTimesheetLogs, $user), $fileName);
            } else {
                return $this->errorResponse(400, "No records found");
            }

        } catch (Exception $e) {
            Log::error($e);
            return $this->errorResponse(500, "Something is wrong");
        }
    }

    // public function exportWeb()
    // {
    //     $user = User::whereId(2)->first();

    //     $manageTimesheetLogs = TimesheetLog::with([
    //         'timesheet:id,start_date,end_date,description'
    //     ])
    //     ->select(
    //         'id',
    //         'timesheet_id',
    //         'date',
    //         'worked_hours',
    //         'is_holiday_or_on_leave'
    //     )
    //     ->onlyForThisYear()
    //     ->whereUserId(2)
    //     ->orderBy('date')
    //     ->get();

    //     return Excel::download(new ManageTimesheetLogsExports($manageTimesheetLogs, $user), "manage-timesheet-logs.xlsx");
    // }
}
