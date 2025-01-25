<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CategoriaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true; // Cambia esto según tus requisitos de autorización
    }

    /**
     * Obtén las reglas de validación que se aplicarán a la solicitud.
     */
    public function rules(): array
    {
        return [
            'id_restaurante' => 'required|integer|min:1',
            'nombre' => 'required|string|max:100|min:2',
            'imagen' => 'required|image',
        ];
    }

    /**
     * Personaliza los mensajes de error de validación.
     */
    public function messages(): array
    {
        return [
            'id_restaurante.required' => 'El ID del restaurante es obligatorio.',
            'id_restaurante.integer' => 'El ID del restaurante debe ser un número.',
            'id_restaurante.min' => 'El ID del restaurante debe ser al menos 1.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser un texto.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'imagen.required' => 'La imagen es obligatoria.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
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
