<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class WorkFromHomeRequest extends FormRequest
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
        if ($route == "work-from-home-store") {
            return [
                'request_to' => 'required',
                'type' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'reason' => 'required',
                'emg_contact_no' => 'sometimes|nullable|max:10|min:10',
            ];
        } else if ($route == "work-from-home-update") {
            return [
                'request_to' => 'required',
                'type' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'reason' => 'required',
                'emg_contact_no' => 'sometimes|nullable|max:10|min:10',
            ];
        } else if ($route == "all-work-from-home-store") {
            return [
                'user_id' => 'required',
                'type' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'reason' => 'required',
                'emg_contact_no' => 'sometimes|nullable|max:10|min:10',
            ];
        } else if ($route == "all-work-from-home-update") {
            return [
                'user_id' => 'required',
                'type' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'reason' => 'required',
                'emg_contact_no' => 'sometimes|nullable|max:10|min:10',
            ];
        } else {
            return [
                'feedback' => 'required|min:5|max:256',
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
