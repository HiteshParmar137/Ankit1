<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Api\V1\MasterRoutes;
use App\Enums\Statuses;
use App\Enums\UserStatuses;
use App\Enums\UserTypes;
use App\Rules\UserRoleRule;
use App\Traits\ApiResponseTraits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Route;

class MasterRequest extends FormRequest
{
    use ApiResponseTraits;

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

        $superAdminRole = UserTypes::getFormattedCaseKey(
            UserTypes::SUPER_ADMIN->value
        );

        if ($route == MasterRoutes::PROJECT_LIST->value) {
            return [
                'status' => 'nullable|sometimes|in:' . Statuses::getValues(),
                'sort_type' => 'nullable|sometimes|in:asc,desc',
                'sort_column' => 'nullable|sometimes|in:name',
            ];
        } else if ($route == MasterRoutes::HOLIDAY_LIST->value) {
            return [
                'status' => 'nullable|sometimes|in:' . Statuses::getValues(),
                'sort_type' => 'nullable|sometimes|in:asc,desc',
                'sort_column' => 'nullable|sometimes|in:name,date',
            ];
        } else if ($route == MasterRoutes::USER_LIST->value) {
            return [
                'role' => 'nullable|sometimes|exists:roles,name|not_in:' . $superAdminRole,
            ];
        } else {
            return [];
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->errorResponse(400, "Request param is invalid"));
    }
}
