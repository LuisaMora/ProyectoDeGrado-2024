<?php

namespace Tests\Feature\S2;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Platillo;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CambiarEstadoPedidoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();         // Finge los eventos para que no se emitan realmente
        $this->setUpDatosIniciales();
        // Storage::fake('public'); // Simular el sistema de archivos
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

        // Inserción de categorías
        $categorias = ['Otros', 'Desayunos', 'Comida', 'Cena', 'Bebidas', 'Postres'];
        foreach ($categorias as $categoria) {
            Categoria::factory()->create(['nombre' => $categoria]);
        }

        // Inserción de mesas
        Mesa::factory(2)->registrar_a_restaurante(1)->create();

        //  Insertar platillos al restaurante1
        Platillo::factory(10)->asignarMenu(1)->create([
            'id_restaurante' => 1
        ]);

        // Creación de empleado asociado al propietario
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('empleado1', 'empleado1@gmail.com')->create([
                    'tipo_usuario' => 'Empleado'
                ])->id,
            'id_rol' => 1,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951529',
            'direccion' => 'Cochabamba',
            'id_restaurante' => 1
        ]);
        // se crea cocinero
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('cocinero1', 'cocinero1@gmail.com')->create([
                    'tipo_usuario' => 'Empleado'
                ])->id,
            'id_rol' => 3,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951561',
            'direccion' => 'Cochabamba',
            'id_restaurante' => 1
        ]);

        // crear las cuentas de los pedidos
        // Inserción de cuentas
        DB::table('cuentas')->insert([
            [
                'id_mesa' => 1,
                'estado' => 'Abierta',
                'nombre_razon_social' => 'Cliente A',
                'nit' => 12345678,
                'monto_total' => 150.00,
                'created_at' => now(),
                'updated_at' => now(),
                'id_restaurante' =>1
            ],
            [
                'id_mesa' => 2,
                'estado' => 'Abierta',
                'nombre_razon_social' => 'Cliente B',
                'nit' => 87654321,
                'monto_total' => 80.00,
                'created_at' => now(),
                'updated_at' => now(),
                'id_restaurante' =>1
            ]
        ]);


        // Inserción de pedidos a las cuentas
        DB::table('pedidos')->insert([
            [
                'id_cuenta' => 1,
                'tipo' => 'local',
                'id_estado' => 1, // Estado 'En espera'
                'id_empleado' => 1,
                'fecha_hora_pedido' => now(),
                'monto' => 150.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_cuenta' => 2,
                'tipo' => 'llevar',
                'id_estado' => 2, // Estado 'En preparación'
                'id_empleado' => 1,
                'fecha_hora_pedido' => now(),
                'monto' => 80.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Obtener platillos aleatorios
        $platillos = DB::table('platillos')->inRandomOrder()->take(2)->get();
        foreach ([1, 2] as $pedidoId) {
            foreach ($platillos as $platillo) {
                DB::table('plato_pedido')->insert([
                    'id_pedido' => $pedidoId,
                    'id_platillo' => $platillo->id,
                    'detalle' => 'Detalles adicionales aquí',
                    'cantidad' => 1,
                    'precio_fijado' => $platillo->precio,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        // print_r('los platos agregados: '.PlatoPedido::all());

    }

    private function loginComoCocinero(): string
    {
        // Realiza el login del cocinero y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'cocinero1',
            'password' => '12345678',
        ]);

        return $response['token'];
    }

    public function test_cambiar_a_estado_en_preparacion()
    {
        $this->ejecutarCambioEstadoPedido(1, 2, 'En preparación');
    }

    public function test_cambiar_a_estado_listo_para_servir()
    {
        $this->ejecutarCambioEstadoPedido(1, 3, 'Listo para servir');
    }

    public function test_cambiar_a_estado_servido()
    {
        $this->ejecutarCambioEstadoPedido(1, 4, 'Servido');
    }

    public function test_cambiar_a_estado_cancelado()
    {
        $this->ejecutarCambioEstadoPedido(1, 5, 'Cancelado');
    }

    private function ejecutarCambioEstadoPedido($idPedido, $nuevoEstado, $nombreEstado)
    {
        // Iniciar sesión y obtener token
        $token = $this->loginComoCocinero();

        // Realizar solicitud de actualización de estado
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->putJson('/api/plato-pedido/estado', [
            'id_pedido' => $idPedido,
            'id_estado' => $nuevoEstado,
            'id_restaurante' => 1,
        ]);

        // Verificar respuesta de éxito y cambio de estado en DB
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('pedidos', [
            'id' => $idPedido,
            'id_estado' => $nuevoEstado,
        ]);
    }

    public function test_cambiar_estado_pedido_invalido()
    {
        $token = $this->loginComoCocinero();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->putJson('/api/plato-pedido/estado', [
            'id_pedido' => 999, // ID de pedido inexistente
            'id_estado' => 2,
            'id_restaurante' => 1,
        ]);

        $response->assertStatus(404)
            ->assertJson(['status' => 'error', 'message' => 'Platos no encontrados']);
    }

    public function test_cambiar_estado_datos_faltantes()
    {
        $token = $this->loginComoCocinero();

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->putJson('/api/plato-pedido/estado', [
            'id_pedido' => 1,
            'id_restaurante' => 1,
            // Faltante id_estado
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Datos inválidos']);
    }
}
