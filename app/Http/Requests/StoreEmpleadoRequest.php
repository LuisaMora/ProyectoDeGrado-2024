<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEmpleadoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'correo' => ['required', 'email', 'unique:usuarios,correo'],
            'nickname' => ['required', 'string', 'max:255', 'unique:usuarios,nickname'],
            'id_rol' => ['required', 'integer'],
            'fecha_nacimiento' => ['required', 'date'],
            'fecha_contratacion' => ['required', 'date'],
            'ci' => ['required', 'string', 'max:20', 'unique:empleados,ci'],
            'direccion' => ['required', 'string', 'max:255'],
            'foto_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // Máx. 2MB
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo debe ser válido.',
            'correo.unique' => 'El correo ya está registrado.',
            'nickname.required' => 'El nickname es obligatorio.',
            'nickname.unique' => 'El nickname ya está en uso.',
            'id_rol.required' => 'El rol es obligatorio.',
            'id_rol.exists' => 'El rol seleccionado no es válido.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_contratacion.required' => 'La fecha de contratación es obligatoria.',
            'ci.required' => 'El CI es obligatorio.',
            'ci.unique' => 'El CI ya está registrado.',
            'direccion.required' => 'La dirección es obligatoria.',
            'foto_perfil.image' => 'El archivo debe ser una imagen.',
            'foto_perfil.mimes' => 'La imagen debe ser de tipo jpg, jpeg o png.',
            'foto_perfil.max' => 'El tamaño de la imagen no debe superar los 2MB.',
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
