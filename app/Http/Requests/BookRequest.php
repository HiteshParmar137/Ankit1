<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BookRequest extends FormRequest
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
        if ($route == 'book-store') {
            return [
                'name' => 'required|min:3|max:255',
                'author' => 'required|min:3|max:255',
                'isbn_number' => 'required|min:3|max:255',
                'status' => 'required|in:0,1'
            ];
        } elseif ($route == 'book-update') {
            return [
                'name' => 'required|min:3|max:255',
                'author' => 'required|min:3|max:255',
                'isbn_number' => 'required|min:3|max:255',
                'status' => 'required|in:0,1'
            ];
        } elseif ($route == 'request-to-read') {
            return [
                'tentative_return_date' => 'required|date|after:today',
                'comment' => 'nullable|sometimes|min:3|max:255',
            ];
        } elseif ($route == 'request-to-read-book-status') {
            return [
                'status' => 'required|in:1,2',
            ];
        } elseif ($route == 'new-book-store') {
            return [
                'name' => 'required|min:3|max:255',
                'author' => 'required|min:3|max:255',
                'comment' => 'nullable|sometimes|min:3|max:255',
            ];
        } else {
            return [
                'name' => 'required|min:3|max:255',
                'author' => 'required|min:3|max:255',
                'comment' => 'nullable|sometimes|min:3|max:255',
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