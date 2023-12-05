<?php

namespace App\Http\Resources\Api\V1\Role;

use Illuminate\Http\Resources\Json\JsonResource;

class RolePermissionResource extends JsonResource
{

    public function toArray($request)
    {
        $role = $request->request_role;

        return [
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description ?? null,
            'has_permission' => $role->hasPermissionTo($this->name)
        ];
    }
}
