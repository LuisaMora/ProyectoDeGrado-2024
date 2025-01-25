<?php

namespace Tests\Feature\Sprint6;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RegistrarFormularioPreRegistroTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function guarda_registro_con_datos_validos()
    {
        $requestData = [
            'nombre_restaurante' => 'Restaurante de Prueba',
            'nit' => '123456789',
            'latitud' => 10.0,
            'longitud' => 10.0,
            'celular_restaurante' => '123456789',
            'correo_restaurante' => 'correo@restaurante.com',
            'licencia_funcionamiento' => UploadedFile::fake()->create('licencia.pdf', 100),
            'tipo_establecimiento' => 'Cafetería',
            'nombre_propietario' => 'Juan',
            'apellido_paterno_propietario' => 'Pérez',
            'apellido_materno_propietario' => 'González',
            'cedula_identidad_propietario' => '12345678',
            'correo_propietario' => 'propietario@correo.com',
            'fotografia_propietario' => UploadedFile::fake()->image('foto.jpg'),
            'pais' => 'Bolivia',
            'departamento' => 'La Paz',
            'numero_mesas' => 10
        ];

        $response = $this->postJson('/api/pre-registro', $requestData);

        $response->assertStatus(201)
                 ->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('formulario_pre_registro', [
            'nombre_restaurante' => 'Restaurante de Prueba',
            'pais' => 'Bolivia',
        ]);
    }

    /** @test */
    public function falla_si_faltan_datos_requeridos()
    {
        $requestData = [
            // Campos intencionalmente omitidos para probar validaciones
        ];

        $response = $this->postJson('/api/pre-registro', $requestData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'nombre_restaurante',
                     'nit',
                     'latitud',
                     'longitud',
                     'celular_restaurante',
                     'correo_restaurante',
                     'licencia_funcionamiento',
                     'tipo_establecimiento',
                     'nombre_propietario',
                     'apellido_paterno_propietario',
                     'apellido_materno_propietario',
                     'cedula_identidad_propietario',
                     'correo_propietario',
                     'fotografia_propietario',
                     'pais',
                     'departamento',
                     'numero_mesas'
                 ]);
    }

    /** @test */
    public function valida_tipo_y_restricciones_de_datos()
    {
        $requestData = [
            'nombre_restaurante' => 123, // Inválido, debería ser string
            'nit' => 'invalido', // Inválido, debería ser numérico
            'latitud' => 100, // Fuera de rango permitido
            'longitud' => -200, // Fuera de rango permitido
            'celular_restaurante' => str_repeat('1', 21), // Excede el límite de caracteres
            'correo_restaurante' => 'correo_invalido', // Inválido, debería ser correo
            'licencia_funcionamiento' => UploadedFile::fake()->image('licencia.jpg'), // Inválido, debería ser PDF
            'numero_mesas' => 25 // Excede el límite de mesas
            // Otros campos requeridos y válidos
        ];

        $response = $this->postJson('/api/pre-registro', $requestData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'nombre_restaurante',
                     'nit',
                     'latitud',
                     'longitud',
                     'celular_restaurante',
                     'correo_restaurante',
                     'licencia_funcionamiento',
                     'numero_mesas'
                 ]);
    }

    /** @test */
    public function devuelve_error_si_archivo_es_invalido()
    {
        $requestData = [
            'nombre_restaurante' => 'Restaurante Error',
            'licencia_funcionamiento' => 'archivo_invalido',
            'fotografia_propietario' => 'archivo_invalido',
            // Otros campos requeridos aquí
        ];

        $response = $this->postJson('/api/pre-registro', $requestData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'fotografia_propietario',
                     'licencia_funcionamiento',
                 ]);
    }
}
