<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait ValidationTrait
{
    public function getFailedValidationKeys($validator)
    {
        $keys = array_keys($validator->errors()->toArray());
        return $keys;
    }

    public function checkValidationKeysAndThrowExceptionAccordingly(
        Validator $validator,
        string $validationKey,
        int $errorCode = 400,
        string $errorMessage = 'Request param is invalid',
        object|null $data = null
    )
    {
        $getValidationKeys = $this->getFailedValidationKeys($validator);

        if (in_array($validationKey, $getValidationKeys)) {
            $formattedString = str_contains($validationKey, '_id') ? str_replace("_id", "",$validationKey) : $validationKey;
            $formattedString = str_replace("_", " ",$formattedString);
            throw new HttpResponseException($this->errorResponse(400, ucfirst($formattedString) . " not found"));
        } else {
            throw new HttpResponseException($this->errorResponse(
                $errorCode,
                $errorMessage,
                (!isset($data) ? $validator->errors() : $data)
            ));
        }
    }
}
