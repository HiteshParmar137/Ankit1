<?php

namespace App\Http\Resources\Api\V1\ManageTimesheet;

use Illuminate\Http\Resources\Json\JsonResource;

class ManageTimesheetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $userProfile = $this->user->userProfile->profile;

        return [
            "id" => $this->id,
            "start_date" => $this->start_date ?? "N/A",
            "end_date" => $this->end_date ?? "N/A",
            "description" => $this->description ?? "N/A",
            'last_updated_at' => date('Y-m-d H:i:s',strtotime($this->updated_at)),
            "timesheet_logs" => ManageTimesheetLogsListResource::collection($this->logs),
            "profile" => new ManageTimesheetProfileDetailsResource($userProfile),
        ];
    }
}
