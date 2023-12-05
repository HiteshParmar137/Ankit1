<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Api\V1\Routes;
use App\Traits\ApiResponseTraits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Route;

class AuthRequest extends FormRequest
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
    public function rules(): array
    {
        $route = Route::currentRouteName();

        if ($route == Routes::REGISTER->value) {
            return [
                'first_name' => 'required|string|min:3|max:100',
                'last_name' => 'required|string|min:3|max:100',
                'email' => 'required|string|email|max:100|unique:users,email,NULL,id,deleted_at,NULL',
                'phone_number' => 'nullable|sometimes|max:25|unique:users,phone_number,NULL,id,deleted_at,NULL',
                'phone_number_country_code' => 'nullable|sometimes|max:10',
                'password' => 'required|min:6',
            ];
        } elseif ($route == Routes::LOGIN->value) {
            return [
                'email' => 'required|string|email|max:100|exists:users,email,deleted_at,NULL',
                'password' => 'required|min:6',
            ];
        } elseif ($route == Routes::VERIFY_EMAIL->value) {
            return [
                'email_verification_token' => 'required',
            ];
        } elseif ($route == Routes::SEND_OTP_TO_RESET_PASSWORD->value) {
            return [
                'email' => 'required|string|email|max:100|exists:users,email,deleted_at,NULL',
            ];
        } elseif ($route == Routes::VERIFY_RESET_PASSWORD_OTP->value) {
            return [
                'email' => 'required|string|email|max:100|exists:users,email,deleted_at,NULL',
                'otp' => 'required|digits:6',
            ];
        } elseif ($route == Routes::RESET_PASSWORD->value) {
            return [
                'email' => 'required|string|email|max:100|exists:users,email,deleted_at,NULL',
                'password' => 'required|min:6|confirmed',
                'password_confirmation' => 'required|min:6',
            ];
        } elseif ($route == Routes::CHANGE_PASSWORD->value) {
            return [
                'current_password' => 'required|min:6|current_password',
                'new_password' => 'required|min:6|confirmed',
                'new_password_confirmation' => 'required|min:6',
            ];
        } elseif ($route == Routes::PROFILE_UPDATE->value) {
            return [
                'first_name' => 'required|string|min:3|max:100',
                'last_name' => 'required|string|min:3|max:100',
            ];
        } else {
            return [];
        }
    }

    protected function failedValidation(Validator $validator): HttpResponseException
    {
        throw new HttpResponseException($this->errorResponse(422, "Validation errors", $validator->errors()));
    }

    public function messages()
    {
        return [
            'current_password.current_password' => 'The current password is incorrect'
        ];
    }
}
