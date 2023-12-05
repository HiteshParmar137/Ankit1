<?php

namespace App\Http\Resources\Api\V1\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UsersListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $roleName = $this->getRoleNames()[0];
        $roleDisplayName = $this->getRoleDisplayNames()[0];

        return [
            "id" => $this->id,
            "first_name" => $this->first_name ?? "N/A",
            "last_name" => $this->last_name ?? "N/A",
            "full_name" => $this->full_name ?? "N/A",
            "email" => $this->email ?? "N/A",
            'phone_number' => $this->phone_number ?? null,
            'phone_number_country_code' => $this->phone_number_country_code ?? null,
            'status' => $this->status ?? null,
            'can_work_in_aws' => $this->can_work_in_aws ?? null,
            'is_email_verified' => !empty($this->email_verified_at) ? true : false,
            'role_name' => $roleName,
            'role_display_name' => $roleDisplayName,
        ];
    }
}
