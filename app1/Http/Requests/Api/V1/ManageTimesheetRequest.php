<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Api\V1\Routes;
use App\Enums\Days;
use App\Helper\Helpers;
use App\Models\User;
use App\Rules\ValidEmailsRule;
use App\Rules\CheckDateRangeRule;
use App\Rules\CheckWeekDayRule;
use App\Traits\ApiResponseTraits;
use App\Traits\ValidationTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class ManageTimesheetRequest extends FormRequest
{
    use ApiResponseTraits, ValidationTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $route = Route::currentRouteName();

        if ($route == Routes::MANAGE_TIMESHEET_UPDATE->value) {
            $dates = Helpers::getAllDatesBetweenTwoDates(
                request()->start_date,
                request()->end_date
            );

            return [
                'user_id' => [
                    'required',
                    'exists:users,id,deleted_at,NULL',
                ],
                'start_date' => [
                    'required',
                    'date',
                    'date_format:Y-m-d',
                    new CheckWeekDayRule(Days::MONDAY->value)
                ],
                'end_date' => [
                    'required',
                    'date',
                    'date_format:Y-m-d',
                    'after:start_date',
                    new CheckWeekDayRule(Days::SUNDAY->value),
                    new CheckDateRangeRule(request()->start_date)
                ],
                'description' => 'required|max:200',
                'timesheet_logs' => 'required|array|min:7|max:7',
                'timesheet_logs.*.date' => 'required|date_format:Y-m-d|in:' . $dates->implode(','),
                'timesheet_logs.*.worked_hours' => 'required|numeric',
                'timesheet_logs.*.is_holiday_or_on_leave' => 'required|integer|in:0,1',
            ];
        } else if($route == Routes::MANAGE_TIMESHEET_HISTORY->value) {
            return [
                'user_id' => [
                    'required',
                    'exists:users,id,deleted_at,NULL',
                ],
                'sort_type' => 'nullable|sometimes|in:asc,desc',
                'sort_column' => 'nullable|sometimes|in:id,start_date',
            ];
        } else if($route == Routes::MANAGE_TIMESHEET->value) {
            return [
                'user_id' => [
                    'required',
                    'exists:users,id,deleted_at,NULL',
                ],
                'start_date' => 'nullable|sometimes|date|date_format:Y-m-d',
                'end_date' => 'nullable|sometimes|date|date_format:Y-m-d|after:start_date',
            ];
        } else if($route == Routes::MANAGE_TIMESHEET_SEND->value) {
            return [
                'user_id' => [
                    'required',
                    'exists:users,id,deleted_at,NULL',
                ],
                'timesheet_id' => [
                    'required',
                    Rule::exists('timesheets', 'id')->where(function ($query) {
                        $currentUser = User::find(request()->user_id);
                        $currentUserId = $currentUser->id ?? null;

                        return $query->where('user_id', $currentUserId)
                            ->whereNull('deleted_at');
                        })
                ],
                'emails' => ['required', new ValidEmailsRule()],
            ]; 
        } else if($route == Routes::MANAGE_TIMESHEET_EXPORT->value) {
            return [
                'user_id' => [
                    'required',
                    'exists:users,id,deleted_at,NULL',
                ],
            ];
        } else {
            return [];
        }
    }

    protected function failedValidation(Validator $validator): HttpResponseException
    {
        $route = Route::currentRouteName();

        if (
            in_array($route, [
                Routes::MANAGE_TIMESHEET->value,
                Routes::MANAGE_TIMESHEET_HISTORY->value,
                Routes::MANAGE_TIMESHEET_EXPORT->value
            ])
        ) {
            $this->checkValidationKeysAndThrowExceptionAccordingly($validator, 'user_id');
        } else {
            $this->checkValidationKeysAndThrowExceptionAccordingly($validator, 'user_id', 422, "Validation errors");
        }
    }

    public function messages()
    {
        return [
            'timesheet_logs.*.date.in' => 'Date is invalid.'
        ];
    }
}
