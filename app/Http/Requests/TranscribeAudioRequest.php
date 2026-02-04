<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranscribeAudioRequest extends FormRequest
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
            'audio' => ['required', 'file', 'mimes:webm,mp3,mp4,mpeg,mpga,m4a,wav,ogg', 'max:25600'], // 25MB max
            'language' => ['nullable', 'string', 'size:2'], // ISO 639-1 language code
            'chat_id' => ['nullable', 'integer', 'exists:chats,id'],
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
            'audio.required' => 'El archivo de audio es requerido.',
            'audio.file' => 'Debe ser un archivo de audio valido.',
            'audio.mimes' => 'El formato de audio debe ser webm, mp3, mp4, mpeg, mpga, m4a, wav u ogg.',
            'audio.max' => 'El archivo de audio no puede superar los 25MB.',
            'language.size' => 'El codigo de idioma debe tener 2 caracteres (ej: es, en).',
            'chat_id.exists' => 'El chat especificado no existe.',
        ];
    }
}
