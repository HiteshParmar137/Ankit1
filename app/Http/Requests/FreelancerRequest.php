<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Crypt;

class FreelancerRequest extends FormRequest
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
        if ($route == "freelancer-store") {
            return [
                'first_name' => 'required|min:3|max:256',
                'last_name' => 'required|min:3|max:256',
                'email' => 'required|email|unique:freelancers,email,NULL,id,deleted_at,NULL',
                'contact_number' => 'required|unique:freelancers,contact_number,NULL,id,deleted_at,NULL',
                'address' => 'required|min:3|max:256',
                'document_url' => 'required|url',
                'technologies' => 'required',
            ];
        } else {
            $feelancerId = Crypt::decrypt($this->id);
            return [
                'first_name' => 'required|min:3|max:256',
                'last_name' => 'required|min:3|max:256',
                'email' => 'required|email|unique:freelancers,email,' .$feelancerId .',id,deleted_at,NULL',
                'contact_number' => 'required|unique:freelancers,contact_number,' .$feelancerId .',id,deleted_at,NULL',
                'address' => 'required|min:3|max:256',
                'document_url' => 'required|url',
                'technologies' => 'required',
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
