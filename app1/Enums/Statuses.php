<?php

namespace App\Enums;

use App\Traits\PrepareEnumDataMethods;

enum Statuses: int
{
    use PrepareEnumDataMethods;

    case ACTIVE = 1;
    case INACTIVE = 2;
}
