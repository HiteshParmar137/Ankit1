<?php

namespace App\Http\Resources\Api\V1\Auth;

use App\Enums\UserTypes;
use App\Helper\Helpers;
use App\Http\Resources\Api\V1\Profile\UserProfileDetailsResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
        $permissionNames = $this->getPermissionNames();
        $rolesWithoutSuperAdmin = Helpers::getRolesWithoutSuperAdmin();

        $data = [
            "id" => $this->id,
            "first_name" => $this->first_name ?? "N/A",
            "last_name" => $this->last_name ?? "N/A",
            "full_name" => $this->full_name ?? "N/A",
            "email" => $this->email ?? "N/A",
            'phone_number' => $this->phone_number ?? null,
            'phone_number_country_code' => $this->phone_number_country_code ?? null,
            "can_work_in_aws" => $this->can_work_in_aws ?? null,
            "status" => $this->status ?? null,
            'role_name' => $roleName,
            'role_display_name' => $roleDisplayName,
            'permissions' => $permissionNames
        ];

        if (in_array($roleName, $rolesWithoutSuperAdmin)) {
            $data['profile'] = new UserProfileDetailsResource($this->userProfile->profile);
        }

        return $data;
    }
}
