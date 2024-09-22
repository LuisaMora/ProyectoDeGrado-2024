<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRestauranteRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cambia esto según tus necesidades de autorización
    }

    public function rules()
    {
        return [
            'id_menu' => 'nullable|integer|exists:menus,id',
            'nombre' => 'required|string|max:100',
            'nit' => 'required|integer|unique:restaurantes,nit',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'celular' => 'required|string|max:20',
            'correo' => 'required|string|email|max:100',
            'licencia_funcionamiento' => 'required|string|max:100',
            'tipo_establecimiento' => 'required|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del restaurante es obligatorio.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.unique' => 'Este NIT ya está registrado.',
            'latitud.required' => 'La latitud es obligatoria.',
            'longitud.required' => 'La longitud es obligatoria.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El formato del correo es incorrecto.',
            // Añade otros mensajes personalizados aquí
        ];
    }
}
