<?php

namespace App\Http\Resources\Api\V1\Holiday;

use Illuminate\Http\Resources\Json\JsonResource;

class HolidayDetailsResource extends JsonResource
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
            "name" => $this->name ?? "N/A",
            "date" => $this->date ?? "N/A",
            "description" => $this->description ?? "N/A",
            "status" => $this->status ?? null,
        ];
    }
}
