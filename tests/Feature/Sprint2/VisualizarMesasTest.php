<?php

namespace Tests\Feature\Sprint2;

use App\Models\Administrador;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisualizarMesasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatosIniciales();
    }

    private function setUpDatosIniciales(): void
    {
        // Inserción de roles
        DB::table('rol_empleados')->insert([
            ['nombre' => 'Mesero'],
            ['nombre' => 'Cajero'],
            ['nombre' => 'Cocinero'],
        ]);

        // Insertar los estados del pedido
        $estados = [
            ['nombre' => 'En espera'],
            ['nombre' => 'En preparación'],
            ['nombre' => 'Listo para servir'],
            ['nombre' => 'Servido'],
        ];

        foreach ($estados as $estado) {
            EstadoPedido::create($estado);
        }

        // Creación de usuarios
        Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        Propietario::factory()->asignarDatosSesion('propietarioA1', 'propietario@gmail.com')->create();

        // Inserción de mesas
        Mesa::factory(8)->registrar_a_restaurante(1)->create();

        // Creación de empleado asociado al propietario
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('mesero1', 'empleado1@gmail.com')->create()->id,
            'id_rol' => 1,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951529',
            'direccion' => 'Cochabamba',
        ]);
        Empleado::create([
            'id_usuario' => User::factory()->asignarNicknameCorreo('cocinero1', 'cocinero1@gmail.com')->create()->id,
            'id_rol' => 3, // Rol de mesero
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951529',
            'direccion' => 'Cochabamba',
        ]);
    }

    private function loginComoUsuario($usuario, $password): string
    {
        // Realiza el login del propietario y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => $usuario,
            'password' => $password,
        ]);

        return $response['token'];
    }

    public function test_retornar_mesas_para_visualizar_desde_propietario()
    {
        // Probar acceso del propietario
        $tokenPropietario =  $this->loginComoUsuario('propietarioA1', '12345678');
        $responsePropietario = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokenPropietario,
        ])->getJson('/api/restaurante/mesas');

        $responsePropietario->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_retornar_mesas_para_visualizar_desde_empleados()
    {
        // Probar acceso del mesero
        $tokenMesero = $this->loginComoUsuario('mesero1', '12345678');
        $responseMesero = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokenMesero,
        ])->getJson('/api/restaurante/mesas');

        $responseMesero->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Probar acceso del cocinero
        $tokenCocinero =  $this->loginComoUsuario('cocinero1', '12345678');
        $responseCocinero = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokenCocinero,
        ])->getJson('/api/restaurante/mesas');

        $responseCocinero->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_retornar_mesas_para_visualizar_desde_administrador_error()
    {
        // Probar acceso del adminsitrador
        $token = $this->loginComoUsuario('administrador', '12345678');
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/restaurante/mesas');

        $response->assertStatus(403)
            ->assertJson(['status' => 'error']);
    }
}
