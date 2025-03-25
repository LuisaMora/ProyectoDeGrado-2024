<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEstadoPedidoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true; // Permitir la validación
    }

    /**
     * Reglas de validación aplicadas a la solicitud.
     */
    public function rules(): array
    {
        return [
            'id_pedido' => 'required|integer|min:1',
            'id_estado' => 'required|integer|min:1|max:5',
            'id_restaurante' => 'required|integer|min:1',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'id_pedido.required' => 'El campo id_pedido es obligatorio.',
            'id_pedido.integer' => 'El campo id_pedido debe ser un número entero.',
            'id_pedido.min' => 'El campo id_pedido debe ser al menos 1.',

            'id_estado.required' => 'El campo id_estado es obligatorio.',
            'id_estado.integer' => 'El campo id_estado debe ser un número entero.',
            'id_estado.min' => 'El campo id_estado debe ser al menos 1.',
            'id_estado.max' => 'El campo id_estado no puede ser mayor a 5.',

            'id_restaurante.required' => 'El campo id_restaurante es obligatorio.',
            'id_restaurante.integer' => 'El campo id_restaurante debe ser un número entero.',
            'id_restaurante.min' => 'El campo id_restaurante debe ser al menos 1.',
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
