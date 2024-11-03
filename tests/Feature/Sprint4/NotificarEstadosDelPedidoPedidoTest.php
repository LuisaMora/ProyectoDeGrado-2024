<?php

namespace Tests\Feature\Sprint4;

use App\Events\Notificacion as NotificacionEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Notificacion;
use App\Models\Platillo;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class NotificarEstadosDelPedidoPedidoTest extends TestCase
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
        Platillo::factory(10)->asignarMenu(1)->create();

        // Creación de empleado asociado al propietario
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('empleado1', 'empleado1@gmail.com')->create()->id,
            'id_rol' => 1,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951529',
            'direccion' => 'Cochabamba',
        ]);
        // se crea cocinero
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('cocinero1', 'cocinero1@gmail.com')->create()->id,
            'id_rol' => 3,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951561',
            'direccion' => 'Cochabamba',
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
            ],
            [
                'id_mesa' => 2,
                'estado' => 'Abierta',
                'nombre_razon_social' => 'Cliente B',
                'nit' => 87654321,
                'monto_total' => 80.00,
                'created_at' => now(),
                'updated_at' => now(),
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

    private function loginComoCocinero()
    {
        // Realiza el login del cocinero y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'cocinero1',
            'password' => '12345678',
        ]);

        return $response;
    }

    private function loginComoEmpleado()
    {
        $response = $this->postJson('/api/login', [
            'usuario' => 'empleado1',
            'password' => '12345678',
        ]);
        return $response['token'];
    }

    public function test_cambiar_y_notificar_estado_en_preparacion()
    {
        $this->ejecutarCambioEstadoPedido(1, 2, 'En preparación');
    }

    public function test_cambiar_y_notificar_estado_listo_para_servir()
    {
        $this->ejecutarCambioEstadoPedido(1, 3, 'Listo para servir');
    }

    public function test_cambiar_y_notificar_estado_servido()
    {
        $this->ejecutarCambioEstadoPedido(1, 4, 'Servido');
    }

    public function test_cambiar_y_notificar_estado_cancelado()
    {
        $this->ejecutarCambioEstadoPedido(1, 5, 'Cancelado');
    }

    private function ejecutarCambioEstadoPedido($idPedido, $nuevoEstado, $nombreEstado)
    {
        // Iniciar sesión y obtener token
        $cuentaCocinero = $this->loginComoCocinero();
        $token = $cuentaCocinero['token'];
        // Realizar solicitud de actualización de estado
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ])->putJson('/api/plato-pedido/estado', [
            'id_pedido' => $idPedido,
            'id_estado' => $nuevoEstado,
            'id_restaurante' => 1,
        ]);

        // Verificar respuesta de éxito y cambio de estado en DB
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
        $idRestaurante = 1;
        $idUsuario = $cuentaCocinero['user']['usuario']['id'];

        Event::assertDispatched(NotificacionEvent::class, function ($event) use ($idUsuario, $idRestaurante) {
            return ($event->id_creador === $idUsuario);
        });

        $this->assertDatabaseHas('notificaciones', [
            'id_pedido' => $idPedido,
            'id_creador' => $idUsuario,
            'id_restaurante' => $idRestaurante,
            'tipo' => 'pedido',
            'read_at' => null, // Cambia esto según tu lógica
        ]);
    }

    public function test_obtener_notificaciones()
    {
        $token = $this->loginComoEmpleado();

        // Notificaciones para el test
        Notificacion::factory()->count(5)->create([
            'id_restaurante' => 1,
            'id_creador' => 3, // usuario id 3
            'id_pedido' => 1,
        ]);

        // Hacer una solicitud para obtener notificaciones
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/notificaciones?id_restaurante=1'); // Enviando el id_restaurante como parámetro de consulta

        // Verificar que la respuesta sea exitosa y que contenga las notificaciones
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'notificaciones']);
    }

    public function test_obtener_notificaciones_cantidad()
    {
        $token = $this->loginComoEmpleado();

        // Crea notificaciones para el test
        Notificacion::factory()->count(5)->create([
            'id_restaurante' => 1,
            'id_creador' => 3, // usuario id 3
            'id_pedido' => 1,
        ]);

        // Hacer una solicitud para obtener una cantidad específica de notificaciones
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/notificaciones/cantidad?id_restaurante=1&cantidad=3'); // Pasar parámetros en la URL

        // Verificar que la respuesta sea exitosa y que contenga las notificaciones
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'notificaciones'])
            ->assertJsonCount(3, 'notificaciones'); // Verifica que se devuelvan exactamente 3 notificaciones
    }

    public function test_marcar_notificaciones_como_leidas()
    {
        $token = $this->loginComoEmpleado();

        // Crea notificaciones para el test
        $notificaciones = Notificacion::factory()->count(5)->create([
            'id_restaurante' => 1,
            'id_creador' => 3, // usuario id 3
            'id_pedido' => 1,
        ]);

        // Hacer una solicitud para marcar las notificaciones como leídas
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->putJson('/api/notificaciones/leidas', [
            'id_notificaciones' => [$notificaciones[0]->id, $notificaciones[1]->id],
            'id_restaurante' => 1,
        ]);
        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Verificar que las notificaciones se hayan marcado como leídas
        foreach ([$notificaciones[0]->id, $notificaciones[1]->id] as $id) {
            $this->assertDatabaseHas('notificaciones', [
                'id' => $id,
                'read_at' => now()->format('Y-m-d H:i:s'), // Verifica que read_at se actualice correctamente
            ]);
        }
    }
}
