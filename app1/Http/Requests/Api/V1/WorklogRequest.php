<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Api\V1\Routes;
use App\Helper\Helpers;
use App\Traits\ApiResponseTraits;
use App\Traits\ValidationTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class WorklogRequest extends FormRequest
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

        $user = Helpers::getLoginUser();

        if ($route == Routes::WORKLOG_ADD->value) {
            return [
                'project_id' => 'required|exists:projects,id,deleted_at,NULL',
                'date' => [
                    'required',
                    'date_format:Y-m-d',
                    Rule::unique('worklogs', 'date')->where(function ($query) use ($user) {
                        return $query->where('user_id', $user->id)
                            ->where('project_id', request()->project_id)
                            ->whereNull('deleted_at');
                        })
                ],
                'worked_hours' => 'required|numeric',
                'description' => 'required|max:200',
            ];
        } else if ($route == Routes::WORKLOG_UPDATE->value) {
            $worklogId = $request->worklog_id ?? null;

            return [
                'worklog_id' => 'required|exists:worklogs,id,deleted_at,NULL',
                'project_id' => 'required|exists:projects,id,deleted_at,NULL',
                'date' => [
                    'required',
                    'date_format:Y-m-d',
                    Rule::unique('worklogs', 'date')->where(function ($query) use ($user) {
                        return $query->where('user_id', $user->id)
                            ->where('project_id', request()->project_id)
                            ->whereNull('deleted_at');
                        })->ignore($worklogId)
                ],
                'worked_hours' => 'required|numeric',
                'description' => 'required|max:200',
            ];
        } else if($route == Routes::WORKLOG_LIST->value){
            return [
                'project_id' => 'nullable|sometimes|exists:projects,id,deleted_at,NULL',
                'from_date' => 'nullable|sometimes|date|date_format:Y-m-d',
                'to_date' => 'nullable|sometimes|date|date_format:Y-m-d|after:from_date',
                'sort_type' => 'nullable|sometimes|in:asc,desc',
                'sort_column' => 'nullable|sometimes|in:date',
            ];
        } else {
            return [];
        }
    }

    protected function failedValidation(Validator $validator): HttpResponseException
    {
        $route = Route::currentRouteName();

        if ($route == Routes::WORKLOG_LIST->value) {
            throw new HttpResponseException($this->errorResponse(400, "Request param is invalid"));
        } else {
            $getValidationKeys = $this->getFailedValidationKeys($validator);
            if (in_array('worklog_id', $getValidationKeys)) {
                throw new HttpResponseException($this->errorResponse(400, "Worklog not found"));
            } else {
                throw new HttpResponseException($this->errorResponse(422, "Validation errors", $validator->errors()));
            }
        }
    }

    public function messages()
    {
        return [
            'date.unique' => 'It\'s look like worklog already available for the given date.',
        ];
    }
}
