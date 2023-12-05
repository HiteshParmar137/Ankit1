<?php

namespace App\Http\Resources\Api\V1\Profile;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileDetailsResource extends JsonResource
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
            "default_hours" => $this->default_hours ?? "N/A",
            "users" => $this->users ?? null,
            "description" => $this->description ?? "N/A",
        ];
    }
}
