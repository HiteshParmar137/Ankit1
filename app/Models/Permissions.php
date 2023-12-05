<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permissions extends SpatiePermission
{
    protected $fillable = ['name', 'display_name', 'description', 'guard_name'];
}