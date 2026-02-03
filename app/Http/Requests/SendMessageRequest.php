<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'El mensaje es requerido',
            'content.max' => 'El mensaje no puede exceder 2000 caracteres',
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
