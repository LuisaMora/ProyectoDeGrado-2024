<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReporteRequest extends FormRequest
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
            'id_restaurante.required' => 'El id del restaurante es requerido',
            'id_restaurante.integer' => 'El id del restaurante debe ser un número entero',
            'id_restaurante.min' => 'El id del restaurante debe ser mayor a 0',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida',
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
            'id_restaurante' => 'required|integer|min:1',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
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
