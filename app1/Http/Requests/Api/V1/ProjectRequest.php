<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Api\V1\Routes;
use App\Enums\Statuses;
use App\Traits\ApiResponseTraits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Route;

class ProjectRequest extends FormRequest
{
    use ApiResponseTraits;

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
    public function rules()
    {
        $route = Route::currentRouteName();

        if ($route == Routes::PROJECT_LIST->value) {
            return [
                'status' => 'nullable|sometimes|in:' . Statuses::getValues(),
                'sort_type' => 'nullable|sometimes|in:asc,desc',
                'sort_column' => 'nullable|sometimes|in:name,status',
            ];
        } else {
            return [];
        }
    }

    protected function failedValidation(Validator $validator): HttpResponseException
    {
        $route = Route::currentRouteName();

        if ($route == Routes::PROJECT_LIST->value) {
            throw new HttpResponseException($this->errorResponse(400, "Request param is invalid"));
        } else {
            throw new HttpResponseException($this->errorResponse(422, "Validation errors", $validator->errors()));
        }
    }
}
