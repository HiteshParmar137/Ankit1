<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Api\V1\Routes;
use App\Enums\Statuses;
use App\Traits\ApiResponseTraits;
use App\Traits\ValidationTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class HolidayRequest extends FormRequest
{
    use ApiResponseTraits, ValidationTrait;
    
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
     * @return array<string, mixed>
     */
    public function rules(Request $request)
    {
        $route = Route::currentRouteName();

        if ($route == Routes::HOLIDAY_ADD->value) {
            return [
                'name' => 'required|string|min:3|max:100',
                'date' => 'required|date_format:Y-m-d|unique:holidays,date,NULL,id,deleted_at,NULL',
                // 'description' => 'required|max:200',
                'status' => 'required|sometimes|in:' . Statuses::getValues(),
            ];
        } else if ($route == Routes::HOLIDAY_UPDATE->value) {
            $holidayId = $request->holiday_id ?? null;

            return [
                'holiday_id' => 'required|exists:holidays,id,deleted_at,NULL',
                'name' => 'required|string|min:3|max:100',
                'date' => 'required|date_format:Y-m-d|unique:holidays,date,' . $holidayId . ',id,deleted_at,NULL',
                // 'description' => 'required|max:200',
                'status' => 'required|sometimes|in:' . Statuses::getValues(),
            ];
        } else if($route == Routes::HOLIDAY_LIST->value){
            return [
                'status' => 'nullable|sometimes|in:' . Statuses::getValues(),
                'sort_type' => 'nullable|sometimes|in:asc,desc',
                'sort_column' => 'nullable|sometimes|in:name,date',
            ];
        } else {
            return [];
        }
    }

    protected function failedValidation(Validator $validator): HttpResponseException
    {
        $route = Route::currentRouteName();

        if ($route == Routes::HOLIDAY_LIST->value) {
            throw new HttpResponseException($this->errorResponse(400, "Request param is invalid"));
        } else {
            $getValidationKeys = $this->getFailedValidationKeys($validator);
            if (in_array('holiday_id', $getValidationKeys)) {
                throw new HttpResponseException($this->errorResponse(400, "Holiday not found"));
            } else {
                throw new HttpResponseException($this->errorResponse(422, "Validation errors", $validator->errors()));
            }
        }
    }

    // public function messages()
    // {
    //     return [];
    // }
}
