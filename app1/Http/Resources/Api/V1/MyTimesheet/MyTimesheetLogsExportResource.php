<?php

namespace App\Http\Resources\Api\V1\MyTimesheet;

use App\Enums\Days;
use App\Helper\Helpers;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MyTimesheetLogsExportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $holidays = Holiday::pluck('name', 'date')->toArray();

        return [
            "date" => Helpers::formatDate($this->date, 'n-j-y') ?? "N/A",
            "day" => substr(Carbon::createFromFormat('Y-m-d', $this->date)->format('l'), 0, 3) ?? "N/A",
            "billed_hours" => $this->getBilledHours($this),
            "description" => $this->getDescription($this, $holidays),
            "monthly_billed_total" => null,
        ];
    }

    private function getBilledHours(object $log)
    {
        /*
        Commenting out : 10/3/23
        if ($log->is_holiday_or_on_leave) {
            if (
                in_array(Carbon::createFromFormat('Y-m-d', $log->date)->format('l'),
                [Days::SUNDAY->value, Days::SATURDAY->value]) &&
                ($log->worked_hours != 0)
            ) {
                return $log->worked_hours;
            } else {
                return null;
            }
        }
        return $log->worked_hours;
        */

        return ($log->worked_hours ? $log->worked_hours : null); 
    }

    private function getDescription(object $log, array $holidays)
    {
        /**
         * 1. If any day is holiday and worked hours is 0 then description = The description will be the name of the holiday.
         * 2. If any day is holiday and worked hours not 0  then description = The description will still be the holiday name.  
         * 3. If user is on leave and worked hours is 0 then description = Personal Time Off (PTO).
         * 4. If user is on leave and worked hours is not 0 then description = This will be the same description as their normal work day.
         * 5. If user is not on leave then description = This will be the same description as their normal work day.
         */

        if ($log->is_holiday_or_on_leave) {
            if (!empty($log->holiday)) {               
                return Helpers::formatDate($log->date, 'F j') . ' - ' . $log->holiday->name;                
            } else if($log->worked_hours != 0) {
                return $log->timesheet->description;
            } else {
                return 'Personal Time Off (PTO)';
            }            
        }  else {
            return ($log->timesheet->description ?? null);
        }
    }
}
