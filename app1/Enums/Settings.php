<?php

namespace App\Enums;

use App\Traits\PrepareEnumDataMethods;

enum Settings: string
{
    use PrepareEnumDataMethods;

    case PROFILE_CODE_PREFIX = 'P00';
    case TOTAL_WEEKDAYS = "7";
}
