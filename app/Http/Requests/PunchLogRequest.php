<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PunchLogRequest extends FormRequest
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
        if ($route == 'punch-log-store') {
            return [
                'user_id' => 'required',
                'date' => 'required|date_format:d-m-Y',
                'log_data' => 'required',
                'log_data.*.in_time' => 'required|date_format:H:i',
                'log_data.*.out_time' => 'required|date_format:H:i',
                'reason' => 'required',
            ];
        } else if($route == 'punch-log-update') {
            return [
                'user_id' => 'required',
                'date' => 'required|date_format:d-m-Y',
                'log_data' => 'required',
                'log_data.*.in_time' => 'required|date_format:H:i',
                'log_data.*.out_time' => 'required|date_format:H:i',
                'reason' => 'required',
            ];
        } else {
            return [
                'date' => 'required|date_format:d-m-Y',
                'bulkUpload' => 'required|mimes:xls,xlsx',
            ];
        }
    }

    protected function failedValidation(Validator $validator)
    {   
        if (request()->ajax()) {
            throw new HttpResponseException(
                response()->json(
                    [
                        'success' => false,
                        'message' => $validator->errors()->first(),
                    ],
                    400
                )
            );
        } else {
            throw new HttpResponseException(
                redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput()
            );
        }
    }
}