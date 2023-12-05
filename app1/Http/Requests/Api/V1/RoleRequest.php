<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Api\V1\Routes;
use App\Traits\ApiResponseTraits;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RoleRequest extends FormRequest
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
        if ($route == Routes::ROLE_UPDATE->value) {
            return [
                'role_name' => 'required|exists:roles,name',
                // 'new_permissions_name' => 'array|min:0'
            ];
        } else {
            return [];
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $route = Route::currentRouteName();
        if ($route == Routes::ROLE_UPDATE->value) {
            throw new HttpResponseException($this->errorResponse(400, "Role not found"));
        }
    }

    // public function messages()
    // {
    //     return [

    //     ];
    // }
}
