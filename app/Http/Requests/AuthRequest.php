<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
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
     * @return array
     */
    public function rules()
    {
        $route = \Route::currentRouteName();

        if ($route == "verify-login") {

            return [
                'email' => 'required|exists:users,email,deleted_at,NULL',
                'password' => 'required|min:6',
            ];
        }  else {
            return [];
        }
    }
    
}
