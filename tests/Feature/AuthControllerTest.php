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

        // Crea un administrador y un propietario
        \App\Models\Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        \App\Models\Propietario::factory()->asignarDatosSesion('test_user', 'propietario@gmail.com')->create();
    }

    /**
     * @covers \App\Http\Controllers\Auth\AuthController::login
     */
    public function test_login_success()
    {
        // Envía una solicitud POST a la ruta de login
        $response = $this->postJson('/api/login', [
            'usuario' => 'test_user',
            'password' => '12345678',
        ]);

        // Verifica la respuesta
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'token',
                     'user',
                     'message',
                 ]);
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
        $response->assertStatus(401)
                 ->assertJsonStructure([
                     'message',
                 ]);
    }

    // public function test_datos_personales
}
