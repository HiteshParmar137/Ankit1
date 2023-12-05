<?php

namespace App\Http\Resources\Api\V1\Worklog;

use Illuminate\Http\Resources\Json\JsonResource;

class WorklogListResource extends JsonResource
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
            "date" => $this->date ?? "N/A",
            "worked_hours" => $this->worked_hours ?? "N/A",
            "description" => $this->description ?? "N/A",
            "project_id" => $this->project_id ?? null,
            "project_name" => $this->project_name ?? "N/A",
        ];
    }
}
