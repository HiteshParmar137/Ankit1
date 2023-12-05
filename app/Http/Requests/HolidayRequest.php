<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class HolidayRequest extends FormRequest
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
        if ($route == "holiday-store") {

            return [
                'name' => 'required|min:3',
                'date' => 'required',
                'description' => 'required|min:3',
                'document_url' => 'nullable|sometimes|url'
            ];
        } else {
            return [
                'name' => 'required|min:3',
                'date' => 'required',
                'description' => 'required|min:3',
                'document_url' => 'nullable|sometimes|url'
            ];
        }
    }

    protected function failedValidation(Validator $validator)
    {
        if (request()->ajax()) {
            throw new HttpResponseException(response()->json(['success' => false, 'message' => $validator->errors()->first()], 400));
        } else {
            throw new HttpResponseException(redirect()->back()->withErrors($validator)->withInput());
        }
    }
}
