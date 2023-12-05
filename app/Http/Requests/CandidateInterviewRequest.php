<?php

namespace App\Http\Requests;

use App\Models\CandidateDetail;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CandidateInterviewRequest extends FormRequest
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
        $time = ($requestDate == Carbon::now()->format('d-m-Y')) ? 'required|after:' . Carbon::now()->format('H:i') : 'required';
        
        if ($route == 'candidate-interview-schedule-store') {
            return [
                'user_id' => 'required|exists:users,id,deleted_at,NULL',
                'date' => 'required',
                'time' => $time,
                'interview_stages_id' => 'required',
            ];
        } else if($route == 'candidate-interview-schedule-store') {
            return [
                'user_id' => 'required|exists:users,id,deleted_at,NULL',
                'date' => 'required',
                'time' => 'required',
                'interview_stages_id' => 'required',
            ];
        } else {
            return [
                'status' => 'required|in:0,1',
                'feedback' => 'required|min:3',
                'eligible_for_future_hiring' => 'required|in:1,2,3',
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