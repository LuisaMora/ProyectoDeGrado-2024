<?php

namespace Tests\Feature\S3;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Propietario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModificarDatosPropietarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatosIniciales();
        Storage::fake('public');
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
            ['nombre' => 'Cancelado'],
        ];

        foreach ($estados as $estado) {
            EstadoPedido::create($estado);
        }
        // Creación de usuarios
        Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        Propietario::factory()->asignarDatosSesion('propietarioA1', 'propietario@gmail.com')->create();

        // Inserción de mesas
        Mesa::factory(2)->registrar_a_restaurante(1)->create();

        Propietario::factory()->asignarDatosSesion('propietarioA2', 'propietario2@gmail.com')->create();

        // Inserción de mesas
        Mesa::factory(2)->registrar_a_restaurante(2)->create();

        Propietario::factory()->asignarDatosSesion('propietarioA3', 'propietario3@gmail.com')->create();

        // Inserción de mesas
        Mesa::factory(2)->registrar_a_restaurante(3)->create();

   
    }

    private function loginComoPropietario()
    {
        // Realiza el login del propietario y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);

        return $response;
    }

    public function test_cambiar_datos_personales_propietario()
    {
        // Loguear como propietario para obtener el token
        $usuarioPropietario = $this->loginComoPropietario();

        $tokenPropietario = $usuarioPropietario['token'];

        // Llamar a la ruta para actualizar los datos del propietario
        $response = $this->withHeader('Authorization', "Bearer $tokenPropietario")
            ->postJson('/api/actualizar/datos-personales', [
                'nombre' => 'María',
                'apellido_paterno' => 'Fernández',
                'apellido_materno' => 'Sánchez',
                'nickname' => 'mari',
                'correo' => 'maria.fernandez@example.com', // Opcional según tu lógica
            ]);

        // Verificar respuesta
        $response->assertStatus(200);
        $this->assertEquals('Datos actualizados correctamente', $response->json('message'));

        // Aserciones para verificar que los datos se han actualizado en la base de datos
        $this->assertDatabaseHas('usuarios', [
            'id' => $usuarioPropietario['user']['usuario']['id'], // Asegúrate de usar el ID del usuario correcto
            'nombre' => 'María',
            'apellido_paterno' => 'Fernández',
            'apellido_materno' => 'Sánchez',
            'nickname' => 'mari',
            'correo' => 'maria.fernandez@example.com', // Verifica que el correo se haya actualizado
        ]);
    }
}
