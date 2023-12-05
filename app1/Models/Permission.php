<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function scopeGetByCanDisabled($query, $canDisabled = 1)
    {
        return $query->where('can_disabled', $canDisabled);
    }
}
