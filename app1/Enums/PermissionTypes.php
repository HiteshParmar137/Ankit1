<?php

namespace App\Enums;

use App\Traits\PrepareEnumDataMethods;

enum PermissionTypes: int
{
    use PrepareEnumDataMethods;

    case CAN_BE_DISABLED = 1;
    case CAN_NOT_BE_DISABLED = 0;
}
