<?php

namespace App\Enums;

use App\Traits\PrepareEnumDataMethods;

enum UserTypes: int
{
    use PrepareEnumDataMethods;

    case SUPER_ADMIN = 1;
    case EMPLOYEE = 2;
    case HR_ADMIN = 3;
}
