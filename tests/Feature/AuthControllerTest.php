<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Carga los datos comunes a todas las pruebas
        DB::table('rol_empleados')->insert([
            ['nombre' => 'Mesero'],
            ['nombre' => 'Cajero'],
            ['nombre' => 'Cocinero'],
        ]);

        // Crea un administrador un propietario y un empleado del propietario
        \App\Models\Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        \App\Models\Propietario::factory()->asignarDatosSesion('propietarioA1', 'propietario@gmail.com')->create();
        \App\Models\Empleado::factory(1)->asignarPropietario(1)->create();
        $empleado = \App\Models\Empleado::where('id_propietario', 1)->first();
        $usuario = $empleado->usuario;
        $usuario->nickname = 'empleadoP1';
        $usuario->save();
    }

    /**
     * @covers \App\Http\Controllers\Auth\AuthController::login
     */
    public function test_login_diferentes_usuarios()
    {
        // Prueba de login para Administrador
        $responseAdmin = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);

        $responseAdmin->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user',
                'message',
            ]);

        // Prueba de login para Propietario
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);

        $responsePropietario->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user',
                'message',
            ]);

        // Prueba de login para Empleado
        $responseEmpleado = $this->postJson('/api/login', [
            'usuario' => 'empleadoP1',
            'password' => '12345678',
        ]);

        $responseEmpleado->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user',
                'message',
            ]);
    }

    public function test_logout()
    {
        $response = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);
        $token = $response['token'];

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
            ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/logout');

        // Verificar respuesta exitosa
        $response->assertStatus(200)
            ->assertJson(['success' => 'Sesion finalizada exitosamente.']);
    }


    public function test_login_entrada_no_valida()
    {
        $response = $this->postJson('/api/login', [
            'usuario' => '',
            'password' => '12345678',
        ]);
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
            ]);
    }

    public function test_login_usuario_incorrecto()
    {
        $response = $this->postJson('/api/login', [
            'usuario' => 'test_user_1',
            'password' => '12345678',
        ]);
        $response->dump();
        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
            ]);
    }

    public function test_datos_personales_no_validos()
    {
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $responsePropietario['token'],
        ])->get('/api/datos-personales');

        $response->assertStatus(400);
    }

    public function test_get_datos_personales()
    {
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $responsePropietario['token'],
        ])->get('/api/datos-personales?id_usuario=' . $responsePropietario['user']['usuario']['id']);

        $response->assertStatus(200);
    }

    public function test_get_datos_personales_error()
    {
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $responsePropietario['token'],
        ])->get('/api/datos-personales?id_usuario=99');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'message',
            ]);
    }
}
