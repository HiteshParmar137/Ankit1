<?php

namespace App\Http\Requests;

use App\Models\CandidateDetail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CandidateDetailsRequest extends FormRequest
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

        $eligibleForFutureHiringDate = $this->eligible_for_future_hiring == CandidateDetail::ELIGIBLE_FOR_FUTURE_HIRING_NOT_SURE || $this->eligible_for_future_hiring == CandidateDetail::ELIGIBLE_FOR_FUTURE_HIRING_YES ? 'required' : 'nullable|sometimes';
        $eligibleForFutureHiringText = $this->eligible_for_future_hiring == CandidateDetail::ELIGIBLE_FOR_FUTURE_HIRING_NOT_SURE || $this->eligible_for_future_hiring == CandidateDetail::ELIGIBLE_FOR_FUTURE_HIRING_YES ? 'required' : 'nullable|sometimes';
        
        if ($route == 'recruitment-store') {
            return [
                'first_name' => 'required|min:3|max:256',
                'last_name' => 'required|min:3|max:256',
                'email' => 'required|email',
                'contact_no' => 'required|min:10|max:10',
                'designation' => '',
                'documents_url' => 'required|url',
                'reference_by' => 'required|min:3|max:256',
                'ctc' => 'required',
                'expected_ctc' => 'required',
                'experience' => 'required',
                'other_details' => 'required|min:3',
                'eligible_for_future_hiring' => 'required|in:0,1,2',
                'eligible_for_future_hiring_date' => $eligibleForFutureHiringDate,
                'eligible_for_future_hiring_text' => $eligibleForFutureHiringText
            ];
        } else {
            return [
                'first_name' => 'required|min:3|max:256',
                'last_name' => 'required|min:3|max:256',
                'email' => 'required|email',
                'contact_no' => 'required|min:10|max:10',
                'designation' => '',
                'documents_url' => 'required|url',
                'reference_by' => 'required|min:3|max:256',
                'ctc' => 'required',
                'expected_ctc' => 'required',
                'experience' => 'required',
                'other_details' => 'required|min:3',
                'eligible_for_future_hiring' => 'required|in:0,1,2',
                'eligible_for_future_hiring_date' => $eligibleForFutureHiringDate,
                'eligible_for_future_hiring_text' => $eligibleForFutureHiringText
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