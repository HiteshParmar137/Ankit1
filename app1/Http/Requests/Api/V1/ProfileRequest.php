<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Api\V1\Routes;
use App\Traits\ApiResponseTraits;
use App\Traits\ValidationTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Route;

class ProfileRequest extends FormRequest
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
    public function rules(): array
    {
        $route = Route::currentRouteName();
        
        if ($route == Routes::PROFILE_ADD->value) {
            return [
                'default_hours' => 'required|numeric',
                'users' => 'required|array|min:1',
                'users.*.name' => 'required|string|min:3|max:100',
                'users.*.email' => 'required|string|email|max:100',
                'description' => 'required',
            ];
        } else if ($route == Routes::PROFILE_UPDATES->value) {
            return [
                'profile_id' => 'required|exists:profiles,id,deleted_at,NULL',
                'default_hours' => 'required|numeric',
                'users' => 'required|array|min:1',
                'users.*.name' => 'required|string|min:3|max:100',
                'users.*.email' => 'required|string|email|max:100',
                'description' => 'required',
            ];
        } else if ($route == Routes::PROFILE_LIST->value) {
            return [
                'sort_type' => 'nullable|sometimes|in:asc,desc',
                'sort_column' => 'nullable|sometimes|in:id,profile_code,default_hours',
            ];
        } else {
            return [];
        }
    }

    protected function failedValidation(Validator $validator): HttpResponseException
    {
        $route = Route::currentRouteName();

        if ($route == Routes::PROFILE_LIST->value) {
            throw new HttpResponseException($this->errorResponse(400, "Request param is invalid"));
        } else {
            $getValidationKeys = $this->getFailedValidationKeys($validator);
            if (in_array('profile_id', $getValidationKeys)) {
                throw new HttpResponseException($this->errorResponse(400, "Profile not found"));
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
