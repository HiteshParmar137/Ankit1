<?php

namespace App\Models;

use Spatie\Permission\Models\Role;
class Roles extends Role
{
    protected $guarded = ['id'];

    const SUPER_ADMIN_ROLE_SLUG = 'super-admin';
    const HUMAN_RESOURCE_EXECUTIVE_ROLE_SLUG = 'human-resource-executive';
    const EMPLOYEE_ROLE_SLUG = 'employee';

    public function ScopeGetTextSearch($query, $text)
    {
        $query->where('name', 'LIKE', "%{$text['character_search']}%")
            ->orWhere('slug', 'like', "%{$text['character_search']}%");
        return $query;
    }
}
