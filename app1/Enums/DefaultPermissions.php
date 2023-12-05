<?php

namespace App\Enums;

use App\Traits\PrepareEnumDataMethods;

enum DefaultPermissions: string
{
    use PrepareEnumDataMethods;

    case DASHBOARD_PERMISSION_NAME = "dashboard";
}
