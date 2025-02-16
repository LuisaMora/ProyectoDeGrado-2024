<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'id_mesa' => 'required|integer|min:1',
            'id_empleado' => 'required|integer|min:1',
            'platillos' => 'required|string',
            'id_restaurante' => 'required|integer',
            'tipo' => 'required|string|in:local,llevar'
        ];
    }

    public function messages()
    {
        return [
            'id_mesa.required' => 'El campo id de la mesa es obligatorio.',
            'id_mesa.integer' => 'El campo id de la mesa debe ser un número entero.',
            'id_mesa.min' => 'El campo id de la mesa debe ser al menos 1.',
            'id_empleado.required' => 'El campo id del empleado es obligatorio.',
            'id_empleado.integer' => 'El campo id del empleado debe ser un número entero.',
            'id_empleado.min' => 'El campo id del empleado debe ser al menos 1.',
            'platillos.required' => 'El campo platillos es obligatorio.',
            'platillos.string' => 'El campo platillos debe ser una cadena de texto.',
            'id_restaurante.required' => 'El campo id del restaurante es obligatorio.',
            'id_restaurante.integer' => 'El campo id del restaurante debe ser un número entero.',
            'tipo.required' => 'El campo tipo es obligatorio.',
            'tipo.string' => 'El campo tipo debe ser una cadena de texto.',
            'tipo.in' => 'El campo tipo debe ser uno de los siguientes valores: local, llevar.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Datos invalidos',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}

