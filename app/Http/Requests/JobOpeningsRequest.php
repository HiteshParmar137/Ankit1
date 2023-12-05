<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class JobOpeningsRequest extends FormRequest
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
        if ($route == "jobOpenings-store") {

            return [
                'name' => 'required|min:3',
                'number_of_position' => 'required|numeric|gt:0',
                'description' => 'nullable|sometimes|min:3|max:255',
            ];
        } else {
            return [
                'name' => 'required|min:3',
                'number_of_position' => 'required|numeric|gt:0',
                'description' => 'nullable|sometimes|min:3|max:255',
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
