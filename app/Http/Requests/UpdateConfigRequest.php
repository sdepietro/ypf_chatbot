<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $configId = $this->route('id');

        return [
            'tag' => 'sometimes|required|string|max:255|unique:configs,tag,' . $configId,
            'value' => 'nullable|string',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'tag.required' => 'El tag es requerido',
            'tag.unique' => 'El tag ya existe',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'data' => null,
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
