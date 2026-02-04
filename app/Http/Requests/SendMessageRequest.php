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
            // Optional STT metadata (when message came from voice)
            'stt_provider' => 'nullable|string|max:50',
            'stt_model' => 'nullable|string|max:50',
            'stt_duration_ms' => 'nullable|integer|min:0',
            'stt_cost' => 'nullable|numeric|min:0',
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
