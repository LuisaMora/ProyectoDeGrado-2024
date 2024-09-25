<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class StorePreRegistroRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nombre_restaurante' => 'required|string|max:100',
            'nit' => 'required|numeric',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'celular_restaurante' => 'required|string|max:20',
            'correo_restaurante' => 'required|email|max:100',
            'licencia_funcionamiento' => 'required|file|mimes:pdf',
            'tipo_establecimiento' => 'required|string|max:100',
            'nombre_propietario' => 'required|string|max:100',
            'apellido_paterno_propietario' => 'required|string|max:100',
            'apellido_materno_propietario' => 'required|string|max:100',
            'cedula_identidad_propietario' => 'required|numeric',
            'correo_propietario' => 'required|email|max:100',
            'fotografia_propietario' => 'required|image',
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
