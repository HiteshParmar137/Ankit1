<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientRequest extends FormRequest
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
        if ($route == 'client-store') {
            return [
                'name' => 'required|min:3|max:255',
                'email_id' => 'required|email|unique:clients,email_id,NULL,id,deleted_at,NULL',
                'skype_id' => 'sometimes',
                'contact_no' => 'sometimes|numeric',
                'linkedIn_url' => 'sometimes|url',
                'website_url' => 'sometimes|url',
                'city' => 'sometimes|min:3|max:255',
                'reference_from' => 'sometimes|min:3|max:255',
                'company_name' => 'sometimes|min:3|max:255',
                'company_email' => 'sometimes|email',
                'company_contact' => 'sometimes|numeric',
                'company_skype' => 'sometimes',
                'company_website' => 'sometimes|url',
                'address' => 'sometimes|min:3|max:255',
                'company_address' => 'sometimes|min:3|max:255',
                'note' => 'sometimes|min:3|max:255',
            ];
        } else {
            return [
                'name' => 'required|min:3|max:255',
                'email_id' => 'required|email',
                'skype_id' => 'sometimes',
                'contact_no' => 'sometimes|numeric',
                'linkedIn_url' => 'sometimes|url',
                'website_url' => 'sometimes|url',
                'city' => 'sometimes|min:3|max:255',
                'reference_from' => 'sometimes|min:3|max:255',
                'company_name' => 'sometimes|min:3|max:255',
                'company_email' => 'sometimes|email',
                'company_contact' => 'sometimes|numeric',
                'company_skype' => 'sometimes',
                'company_website' => 'sometimes|url',
                'address' => 'sometimes|min:3|max:255',
                'company_address' => 'sometimes|min:3|max:255',
                'note' => 'sometimes|min:3|max:255',
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