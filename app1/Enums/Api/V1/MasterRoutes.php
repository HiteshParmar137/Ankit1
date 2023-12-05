<?php

namespace App\Enums\Api\V1;

use App\Traits\PrepareEnumDataMethods;

enum MasterRoutes: string
{
    use PrepareEnumDataMethods;

    case PROJECT_LIST = "api-v1-master-project-list";
    case PROFILE_LIST = "api-v1-master-profile-list";
    case HOLIDAY_LIST = "api-v1-master-holiday-list";
    case USER_LIST = "api-v1-master-user-list";
}
