<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegistroProductoRequest extends FormRequest
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
            'nombre.required' => 'El nombre es requerido.',
            'nombre.max' => 'El nombre no debe ser mayor a 100 caracteres.',
            'descripcion.required' => 'La descripción es requerida.',
            'descripcion.max' => 'La descripción no debe ser mayor a 255 caracteres.',
            'precio.required' => 'El precio es requerido.',
            'precio.numeric' => 'El precio debe ser un número.',
            'imagen.required' => 'La imagen es requerida.',
            'imagen.image' => 'La imagen debe ser un archivo de imagen.',
            'id_categoria.required' => 'La categoría es requerida.',
            'id_categoria.numeric' => 'La categoría debe ser un número.',
            'id_restaurante.required' => 'El restaurante es requerido.',
            'id_restaurante.numeric' => 'El restaurante debe ser un número.',
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
            'nombre' => 'required|max:100',
            'descripcion' => 'required|max:255',
            'precio' => 'required|numeric',
            'imagen' => 'required|image',
            'id_categoria' => 'required|numeric',
            'id_restaurante' => 'required|numeric',
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
