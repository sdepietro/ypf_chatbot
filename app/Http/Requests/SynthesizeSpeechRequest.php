<?php

namespace App\Http\Requests;

use App\Services\Speech\OpenAITTSService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SynthesizeSpeechRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:4096'], // OpenAI TTS has a 4096 character limit
            'voice' => ['nullable', 'string', Rule::in(OpenAITTSService::VOICES)],
            'message_id' => ['nullable', 'integer', 'exists:messages,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'text.required' => 'El texto a sintetizar es requerido.',
            'text.max' => 'El texto no puede superar los 4096 caracteres.',
            'voice.in' => 'La voz seleccionada no es valida. Opciones: ' . implode(', ', OpenAITTSService::VOICES),
            'message_id.exists' => 'El mensaje especificado no existe.',
        ];
    }
}
