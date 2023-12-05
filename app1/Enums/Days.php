<?php

namespace App\Enums;

use App\Traits\PrepareEnumDataMethods;

enum Days: string
{
    use PrepareEnumDataMethods;

    case SUNDAY = "Sunday";
    case MONDAY = "Monday";
    case TUESDAY = "Tuesday";
    case WEDNESDAY = "Wednesday";
    case THURSDAY = "Thursday";
    case FRIDAY = "Friday";
    case SATURDAY = "Saturday";
}
