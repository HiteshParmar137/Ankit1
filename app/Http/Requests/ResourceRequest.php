<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResourceRequest extends FormRequest
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
        if ($route == "resource-store") {
            return [
                'name' => 'required|min:3|max:255',
                'request_to' => 'required'
            ];
        } else if($route == "resource-update") {
            return [
                'name' => 'required|min:3|max:255',
                'request_to' => 'required'
            ];
        } else {
            return [
                'status' => 'required|in:1,2'
            ];
        }
    }

    protected function failedValidation(Validator $validator)
    {
        // $this->ajax();
        if (request()->ajax()) {
            throw new HttpResponseException(response()->json(['success' => false, 'message' => $validator->errors()->first()], 400));
        } else {
            throw new HttpResponseException(redirect()->back()->withErrors($validator)->withInput());
        }
    }
}
