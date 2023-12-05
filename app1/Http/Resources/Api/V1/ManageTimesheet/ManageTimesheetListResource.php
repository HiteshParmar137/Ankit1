<?php

namespace App\Http\Resources\Api\V1\ManageTimesheet;

use App\Helper\Helpers;
use Illuminate\Http\Resources\Json\JsonResource;

class ManageTimesheetListResource extends JsonResource
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
            "start_date" => $this->start_date ?? "N/A",
            "end_date" => $this->end_date ?? "N/A",
            "description" => $this->description ?? "N/A",
            "total_worked_hours" => Helpers::roundValue($this->logs->sum('worked_hours')),
        ];
    }
}
