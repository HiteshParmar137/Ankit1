<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Api\V1\Routes;
use App\Enums\UserStatuses;
use App\Helper\Helpers;
use App\Rules\UserRoleRule;
use App\Traits\ApiResponseTraits;
use App\Traits\ValidationTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class UserRequest extends FormRequest
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
    public function rules(Request $request): array
    {
        $route = Route::currentRouteName();
        
        $roleName = request()->role_name ?? null;

        $rolesWithoutSuperAdmin = Helpers::getRolesWithoutSuperAdmin();

        if ($route == Routes::USER_ADD->value) {
            return [
                'first_name' => 'required|string|min:3|max:100',
                'last_name' => 'required|string|min:3|max:100',
                'email' => 'required|string|email|max:100|unique:users,email,NULL,id,deleted_at,NULL',
                'phone_number' => 'required|max:25|unique:users,phone_number,NULL,id,deleted_at,NULL',
                'phone_number_country_code' => 'required|max:10',
                'status' => 'required|integer|in:' . UserStatuses::getValues(),
                'role_name' => ['required', new UserRoleRule($roleName)],
                'profile_id' => 'required|exists:profiles,id,deleted_at,NULL',
                'can_work_in_aws' => 'required|integer|in:0,1'
            ];
        } elseif ($route == Routes::USER_UPDATE->value) {

            $userId = $request->user_id ?? null;

            return [
                'user_id' => 'required|exists:users,id,deleted_at,NULL',
                'first_name' => 'required|string|min:3|max:100',
                'last_name' => 'required|string|min:3|max:100',
                'email' => 'required|string|email|max:100|unique:users,email,' . $userId . ',id,deleted_at,NULL',
                'phone_number' => 'required|max:25|unique:users,phone_number,' . $userId . ',id,deleted_at,NULL',
                'phone_number_country_code' => 'required|max:10',
                'role_name' => ['required', new UserRoleRule($roleName)],
                'profile_id' => 'required|exists:profiles,id,deleted_at,NULL',
                'can_work_in_aws' => 'required|integer|in:0,1'
            ];
        } else if($route == Routes::USER_LIST->value){
            return [
                'can_work_in_aws' => 'nullable|sometimes|in:0,1',
                'status' => 'nullable|sometimes|in:' . UserStatuses::getValues(),
                'sort_type' => 'nullable|sometimes|in:asc,desc',
                'sort_column' => 'nullable|sometimes|in:first_name,last_name,full_name,email,phone_number,status,can_work_in_aws',
            ];
        } else if($route == Routes::USER_STATUS->value){
            return [
                'user_id' => 'required|exists:users,id,deleted_at,NULL',
                'status' => 'required|integer|in:' .
                    UserStatuses::ACTIVE_USER->value .
                    ',' .
                    UserStatuses::INACTIVE_USER->value,
            ];
        } else {
            return [];
        }
            
    }

    protected function failedValidation(Validator $validator): HttpResponseException
    {
        $route = Route::currentRouteName();

        if ($route == Routes::USER_LIST->value) {
            throw new HttpResponseException($this->errorResponse(400, "Request param is invalid"));
        } else {
            $getValidationKeys = $this->getFailedValidationKeys($validator);
            if (in_array('user_id', $getValidationKeys)) {
                throw new HttpResponseException($this->errorResponse(400, "User not found"));
            } else {
                throw new HttpResponseException($this->errorResponse(422, "Validation errors", $validator->errors()));
            }
        }
    }

    // public function messages()
    // {
    //     return [];
    // }
}
