<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProjectRequest extends FormRequest
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
        if ($route == "project-store") {
            return [
                'type' => 'required',
                'name' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'name' => 'required',
                'technologies' => 'required',
                'status' => 'required',
                'description' => 'required|max:256|min:3',
                'hours' => 'nullable|sometimes',
                'cost' => 'required',
                'cost_type' => 'required',
            ];
        } else if ($route == "project-update") {
            return [
                'type' => 'required',
                'name' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'name' => 'required',
                'technologies' => 'required',
                'status' => 'required',
                'description' => 'required|max:256|min:3',
                'hours' => 'nullable|sometimes',
                'cost' => 'required',
                'cost_type' => 'required',
            ];
        } else {
            return [
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
