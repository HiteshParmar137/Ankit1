<?php

namespace App\Http\Resources\Api\V1\ManageTimesheet;

use Illuminate\Http\Resources\Json\JsonResource;

class ManageTimesheetLogsListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "timesheet_id" => $this->timesheet_id,
            "date" => $this->date ?? "N/A",
            "worked_hours" => $this->worked_hours ?? "N/A",
            "is_holiday_or_on_leave" => $this->is_holiday_or_on_leave ?? null,
        ];
    }
}
