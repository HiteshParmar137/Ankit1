<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Carbon;

class MeetingRequest extends FormRequest
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
        $requestDate = Carbon::parse($this->date)->format('d-m-Y');
        if ($requestDate == Carbon::now()->format('d-m-Y')) {
            $time = 'required|after:' . Carbon::now()->format('H:i');
        } else {
            $time = 'required';
        }
        if ($route == 'meeting-store') {
            return [
                'title' => 'required|min:3|max:255',
                'presenter' => 'required',
                'guest' => 'required',
                'date' => 'required',
                'duration' => 'required',
                'agenda' => 'required|min:3|max:255',
                'time' => $time
            ];
        } elseif($route == 'meeting-update') {
            return [
                'title' => 'required|min:3|max:255',
                'presenter' => 'required',
                'guest' => 'required',
                'date' => 'required',
                'duration' => 'required',
                'agenda' => 'required|min:3|max:255',
                'time' => $time,
            ];
        } elseif ($route == 'meeting-feedback') {
            return [
                'feedback' => 'required|min:3|max:255',
            ];
        } else {
            return [
                'status' => 'in:1,2,3,4',
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