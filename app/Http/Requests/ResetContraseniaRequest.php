<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetContraseniaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'token.min' => 'El token debe tener al menos 60 caracteres.',
            'token.max' => 'El token no puede tener más de 60 caracteres.',
            'oldPassword.min' => 'La contraseña actual debe tener al menos 6 caracteres.',
            'oldPassword.max' => 'La contraseña actual no puede tener más de 60 caracteres.',
            'newPassword.required' => 'La nueva contraseña es obligatoria.',
            'newPassword.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'min:60|max:60',
            'oldPassword' => 'min:6|max:60', // Confirmar que la contraseña es igual en los dos campos
            'newPassword' => 'required|min:6', // Confirmar que la contraseña es igual en los dos campos
        ];
    }
}
