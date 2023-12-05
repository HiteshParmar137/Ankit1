<?php

namespace App\Http\Resources\Api\V1\MyTimesheet;

use Illuminate\Http\Resources\Json\JsonResource;

class MyTimesheetResource extends JsonResource
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
            'last_updated_at' => date('Y-m-d H:i:s',strtotime($this->updated_at)),
            "timesheet_logs" => MyTimesheetLogsListResource::collection($this->logs),
        ];
    }
}
