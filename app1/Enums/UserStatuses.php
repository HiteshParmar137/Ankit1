<?php

namespace App\Enums;

use App\Traits\PrepareEnumDataMethods;

enum UserStatuses: int
{
    use PrepareEnumDataMethods;

    case ACTIVE_USER = 1;
    case INACTIVE_USER = 2;
    case PENDING_USER = 3;
}
