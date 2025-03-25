<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ConfirmarRegistroRestauranteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function messages()
    {
        return [
            'pre_registro_id.required' => 'El pre_registro_id es requerido',
            'pre_registro_id.numeric' => 'El pre_registro_id debe ser un número',
            'pre_registro_id.min' => 'El pre_registro_id debe ser mayor a 0',
            'estado.required' => 'El estado es requerido',
            'estado.string' => 'El estado debe ser un texto',
            'estado.min' => 'El estado debe tener al menos 1 caracter',
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
            'pre_registro_id' => 'required|numeric|min:1',
            'estado' => 'required|string|min:1',
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
