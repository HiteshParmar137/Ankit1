<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProjectAssignRequest extends FormRequest
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
        if ($route == "project-assign-store") {
            return [
                'user_id' => 'required|exists:users,id,deleted_at,NULL',
                'project_id' => 'required|exists:projects,id,deleted_at,NULL',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
            ];
        } else if ($route == "project-assign-update") {
            return [
                'user_id' => 'required|exists:users,id,deleted_at,NULL',
                'project_id' => 'required|exists:projects,id,deleted_at,NULL',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
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
