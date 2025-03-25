<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUsuarioRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize()
    {
        // Aquí puedes agregar lógica para validar si el usuario está autorizado.
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     */
    public function rules()
    {

        $reglas = [
            'nombre' => 'required|max:100|min:2',
            'apellido_paterno' => 'required|max:100|min:2',
            'apellido_materno' => 'required|max:100|min:2',
            'nickname' => 'required|max:100|min:2',
            'foto_perfil' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'correo' => '|email|max:150',
        ];

        return $reglas;
    }

    /**
     * Personaliza los mensajes de error para las validaciones.
     */
    public function messages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio para propietarios.',
            'foto_perfil.image' => 'El archivo debe ser una imagen válida.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
