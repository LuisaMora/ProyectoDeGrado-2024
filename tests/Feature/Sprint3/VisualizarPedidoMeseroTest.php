<?php

namespace Tests\Feature\Sprint3;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Platillo;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisualizarPedidoMeseroTest extends TestCase
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

        
    }

    // Es igual que el registro del pedido en el Sprint2
    private function resgistrarPedidos()
    {
        // Crear platillos y obtener sus IDs
        $platillos = Platillo::factory(3)->asignarMenu(1)->create([
            'id_restaurante' => 1
        ]);
        $platilloIds = $platillos->pluck('id')->toArray();

        // Estructura de datos para el pedido usando los IDs obtenidos
        $data = [
            'id_mesa' => 1,
            'id_empleado' => 1,
            'platillos' => json_encode([
                [
                    'id_platillo' => $platilloIds[0],
                    'cantidad' => 2,
                    'precio_unitario' => 15,
                    'detalle' => 'Este platillo es sin sal'
                ],
                [
                    'id_platillo' => $platilloIds[1],
                    'cantidad' => 1,
                    'precio_unitario' => 100,
                    'detalle' => ''
                ],
                [
                    'id_platillo' => $platilloIds[2],
                    'cantidad' => 3,
                    'precio_unitario' => 5,
                    'detalle' => ''
                ],
            ]),
            'id_restaurante' => 1,
            'tipo' => 'local',
        ];
        $token = $this->loginComoMesero();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pedido', $data);
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['pedido' => ['id', 'tipo', 'fecha_hora_pedido']]);
    }

    private function loginComoMesero(): string
    {
        // Realiza el login del mesero y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'empleado1',
            'password' => '12345678',
        ]);

        return $response['token'];
    }

    public function test_mesero_puede_ver_pedidos_del_dia()
    {
        $this->resgistrarPedidos();
        // Crea un mesero y realiza el login para obtener el token de autorización
        $token = $this->loginComoMesero();

        // Realizar solicitud GET y verificar que la respuesta es correcta
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            // id_empleado/id_restaurante
        ])->getJson("/api/pedidos/1/1"); // Reemplaza con el ID del restaurante según tu setup


        // Verifica que la respuesta sea exitosa y tenga la estructura esperada
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'pedidos' => [
                    '*' => [
                        'id_cuenta',
                        'monto_total',
                        'nombreMesa',
                        'pedidos' => [
                            '*' => [
                                'id_pedido',
                                'estado',
                                'platos' => [
                                    '*' => [
                                        'nombre',
                                        'precio_fijado',
                                        'cantidad',
                                        'detalle'
                                    ]
                                ],
                                'monto'
                            ]
                        ]
                    ]
                ]
            ]);
    }
}
