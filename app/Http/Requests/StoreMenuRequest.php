<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

     /**
     * Mensajes personalizados para cada regla de validación.
     */
    public function messages(): array
    {
        return [
            'id_menu.required' => 'El campo ID del menú es obligatorio.',
            'id_menu.numeric' => 'El ID del menú debe ser un número válido.',

            'tema.required' => 'El tema del menú es obligatorio.',
            'tema.max' => 'El tema no puede tener más de 100 caracteres.',
            'tema.min' => 'El tema debe tener al menos 2 caracteres.',

            'platillos.required' => 'Debe agregar al menos un platillo al menú.',
            'platillos.string' => 'El campo platillos debe ser una cadena de texto.',
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
            'id_menu' => 'required|numeric',
            'tema' => 'required|max:100|min:2',
            'platillos' => 'required|string',
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
