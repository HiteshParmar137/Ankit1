<?php

namespace App\Http\Resources\Api\V1\User;

use App\Enums\UserTypes;
use App\Helper\Helpers;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\Profile\UserProfileDetailsResource;

class UserOptionsResource extends JsonResource
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
        $rolesWithoutSuperAdmin = Helpers::getRolesWithoutSuperAdmin();

        $response =  [
            "id" => $this->id,
            "first_name" => $this->first_name ?? "N/A",
            "last_name" => $this->last_name ?? "N/A",
            "full_name" => $this->full_name ?? "N/A"
        ];

        if (in_array($roleName, $rolesWithoutSuperAdmin)) {
            $response['profile'] = new UserProfileDetailsResource($this->userProfile->profile ?? null);
        }
        
        return $response;
    }
}
