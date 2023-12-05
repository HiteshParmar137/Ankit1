<?php

namespace App\Enums\Api\V1;

use App\Traits\PrepareEnumDataMethods;

enum Routes: string
{
    use PrepareEnumDataMethods;

    case ROLE_LIST = "api-v1-role-list";
    case ROLE_DETAILS = "api-v1-role-details";
    case ROLE_UPDATE = "api-v1-role-update";

    case REGISTER = "api-v1-register";
    case LOGIN = "api-v1-login";
    case VERIFY_EMAIL = "api-v1-verify-email";
    case SEND_OTP_TO_RESET_PASSWORD = "api-v1-send-otp-to-reset-password";
    case VERIFY_RESET_PASSWORD_OTP = "api-v1-verify-reset-password-otp";
    case RESET_PASSWORD = "api-v1-reset-password";
    case CHANGE_PASSWORD = "api-v1-change-password";
    case ME = "api-v1-me";
    case LOGOUT = "api-v1-logout";
    case PROFILE_UPDATE = "api-v1-profile";

    case USER_LIST = "api-v1-user-list";
    case USER_ADD = "api-v1-user-add";
    case USER_DETAILS = "api-v1-user-details";
    case USER_UPDATE = "api-v1-user-update";
    case USER_REMOVE = "api-v1-user-remove";
    case USER_STATUS = "api-v1-user-status";

    case PROJECT_LIST = "api-v1-project-list";

    case HOLIDAY_LIST = "api-v1-holiday-list";
    case HOLIDAY_ADD = "api-v1-holiday-add";
    case HOLIDAY_DETAILS = "api-v1-holiday-details";
    case HOLIDAY_UPDATE = "api-v1-holiday-update";
    case HOLIDAY_REMOVE = "api-v1-holiday-remove";

    case WORKLOG_LIST = "api-v1-worklog-list";
    case WORKLOG_ADD = "api-v1-worklog-add";
    case WORKLOG_DETAILS = "api-v1-worklog-details";
    case WORKLOG_UPDATE = "api-v1-worklog-update";
    case WORKLOG_REMOVE = "api-v1-worklog-remove";

    case PROFILE_LIST = "api-v1-profile-list";
    case PROFILE_ADD = "api-v1-profile-add";
    case PROFILE_DETAILS = "api-v1-profile-details";
    case PROFILE_UPDATES = "api-v1-profile-update";
    case PROFILE_REMOVE = "api-v1-profile-remove";

    case MY_TIMESHEET = "api-v1-my-timesheet-periodic";
    case MY_TIMESHEET_HISTORY = "api-v1-my-timesheet-history";
    case MY_TIMESHEET_UPDATE = "api-v1-my-timesheet-update";
    case MY_TIMESHEET_SEND = "api-v1-my-timesheet-send";
    case MY_TIMESHEET_EXPORT = "api-v1-my-timesheet-export";

    case MANAGE_TIMESHEET = "api-v1-manage-timesheet-periodic";
    case MANAGE_TIMESHEET_HISTORY = "api-v1-manage-timesheet-history";
    case MANAGE_TIMESHEET_UPDATE = "api-v1-manage-timesheet-update";
    case MANAGE_TIMESHEET_SEND = "api-v1-manage-timesheet-send";
    case MANAGE_TIMESHEET_EXPORT = "api-v1-manage-timesheet-export";
}
