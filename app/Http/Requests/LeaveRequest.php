<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LeaveRequest extends FormRequest
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
        if ($route == "leave-store") {
            return [
                'request_to' => 'required|exists:users,id,deleted_at,NULL',
                'leave_type' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'reason' => 'required',
            ];
        } else if ($route == "leave-update") {
            return [
                'request_to' => 'required|exists:users,id,deleted_at,NULL',
                'leave_type' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'reason' => 'required',
            ];
        } else if ($route == "all-leave-store") {
            return [
                'user_id' => 'required|exists:users,id,deleted_at,NULL',
                'leave_type' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'reason' => 'required',
            ];
        } else if ($route == "all-leave-update") {
            return [
                'user_id' => 'required|exists:users,id,deleted_at,NULL',
                'leave_type' => 'required',
                'start_date' => 'required',
                'end_date' => 'required|after_or_equal:start_date',
                'reason' => 'required',
            ];
        } else if ($route == "leave-debited") {
            return [
                'debitedValue' => 'in:0,1'
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
