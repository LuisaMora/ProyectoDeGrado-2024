<?php

namespace Tests\Feature\S2;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Platillo;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrarPedidoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatosIniciales();
        Storage::fake('public'); // Simular el sistema de archivos
        Event::fake();         // Finge los eventos para que no se emitan realmente
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

        // Platillo::factory(10)->asignarMenu(1)->create();
        // Inserción de cuentas
        // DB::table('cuentas')->insert([
        //     [
        //         'id_mesa' => 1,
        //         'estado' => 'Pagada',
        //         'nombre_razon_social' => 'Cliente A',
        //         'nit' => 12345678,
        //         'monto_total' => 150.00,
        //         'created_at' => now()->subDay(),
        //         'updated_at' => now()->subDay(),
        //     ],
        //     [
        //         'id_mesa' => 2,
        //         'estado' => 'Pagada',
        //         'nombre_razon_social' => 'Cliente B',
        //         'nit' => 87654321,
        //         'monto_total' => 80.00,
        //         'created_at' => now()->subDay(),
        //         'updated_at' => now()->subDay(),
        //     ]
        // ]);


        // // Inserción de pedidos a las cuentas
        // DB::table('pedidos')->insert([
        //     [
        //         'id_cuenta' => 1,
        //         'tipo' => 'local',
        //         'id_estado' => 1, // Estado 'En espera'
        //         'id_empleado' => 1,
        //         'fecha_hora_pedido' => now()->subDay(),
        //         'monto' => 150.00,
        //         'created_at' => now()->subDay(),
        //         'updated_at' => now()->subDay(),
        //     ],
        //     [
        //         'id_cuenta' => 2,
        //         'tipo' => 'llevar',
        //         'id_estado' => 2, // Estado 'En preparación'
        //         'id_empleado' => 1,
        //         'fecha_hora_pedido' => now()->subDay(),
        //         'monto' => 80.00,
        //         'created_at' => now()->subDay(),
        //         'updated_at' => now()->subDay(),
        //     ]
        // ]);

        // // Obtener platillos aleatorios
        // $platillos = DB::table('platillos')->inRandomOrder()->take(2)->get();
        // foreach ([1, 2] as $pedidoId) {
        //     foreach ($platillos as $platillo) {
        //         DB::table('plato_pedido')->insert([
        //             'id_pedido' => $pedidoId,
        //             'id_platillo' => $platillo->id,
        //             'detalle' => 'Detalles adicionales aquí',
        //             'cantidad' => 1,
        //             'precio_fijado' => $platillo->precio,
        //             'created_at' => now()->subDay(),
        //             'updated_at' => now()->subDay(),
        //         ]);
        //     }
        // }
    }

    private function loginComoMesero(): string
    {
        // Realiza el login del propietario y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'empleado1',
            'password' => '12345678',
        ]);

        return $response['token'];
    }

    public function test_show_solo_platillos_disponibles_para_pedidos()
    {
        // Crear platillos: uno disponible y habilitado en el menú, y otros no
        $platilloDisponible = Platillo::factory()->create([
            'id_menu' => 1,
            'disponible' => true,
            'plato_disponible_menu' => true,
            'id_restaurante' => 1,
        ]);
        // plato eliminado
        Platillo::factory()->create([
            'id_menu' => 1,
            'disponible' => false,
            'plato_disponible_menu' => true,
            'id_restaurante' => 1,
        ]);
        // plato no habilitado en el manu
        Platillo::factory()->create([
            'id_menu' => 1,
            'disponible' => true,
            'plato_disponible_menu' => false,
            'id_restaurante' => 1,
        ]);

        $token = $this->loginComoMesero();

        // Realizar petición para mostrar platillos disponibles
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/menu/pedido/platillos/1");

        // Obtener los IDs de los platillos que deberían estar en la respuesta
        $expectedIds = [$platilloDisponible->id];

        // Verificar que la respuesta contiene solo los IDs de platillos disponibles
        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success'])
            ->assertJsonCount(count($expectedIds), 'platillo')
            ->assertJsonPath('platillo.*.id', $expectedIds);
    }

    public function test_store_pedido_guarda_correctamente()
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

        // Realizar petición para mostrar platillos disponibles
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pedido', $data);

        // Verificar que el pedido se guardó en la base de datos
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['pedido' => ['id', 'tipo', 'fecha_hora_pedido']]);

        // Calcular el monto total esperado
        $expectedMontoTotal = (2 * 15) + (1 * 100) + (3 * 5);

        // Verificar que el pedido tenga el monto esperado
        $pedidoId = $response->json('pedido.id');
        $this->assertDatabaseHas('pedidos', [
            'id' => $pedidoId,
            'monto' => $expectedMontoTotal,
        ]);

        // Verificar que la cuenta asociada tenga el monto_total esperado
        $cuentaId = $response->json('pedido.id_cuenta');
        $this->assertDatabaseHas('cuentas', [
            'id' => $cuentaId,
            'monto_total' => $expectedMontoTotal,
        ]);
    }

    public function test_store_pedido_falla_con_datos_incorrectos()
    {
        // Datos de prueba con errores de validación
        $invalidDataSets = [
            [
                'data' => [ // Faltan id_mesa y tipo
                    'id_empleado' => 1,
                    'platillos' => json_encode([
                        ['id_platillo' => 1, 'cantidad' => 2, 'precio_unitario' => 15],
                    ]),
                    'id_restaurante' => 1,
                ],
                'errors' => ['id_mesa', 'tipo'],
            ],
            [
                'data' => [ // id_mesa no es un entero positivo
                    'id_mesa' => 0,
                    'id_empleado' => 1,
                    'platillos' => json_encode([
                        ['id_platillo' => 1, 'cantidad' => 2, 'precio_unitario' => 15],
                    ]),
                    'id_restaurante' => 1,
                    'tipo' => 'llevar',
                ],
                'errors' => ['id_mesa'],
            ],
            [
                'data' => [ // tipo con un valor no válido
                    'id_mesa' => 1,
                    'id_empleado' => 1,
                    'platillos' => json_encode([
                        ['id_platillo' => 1, 'cantidad' => 2, 'precio_unitario' => 15],
                    ]),
                    'id_restaurante' => 1,
                    'tipo' => 'enviado', // Valor no permitido
                ],
                'errors' => ['tipo'],
            ],
            // Agrega más casos de prueba según sea necesario
        ];

        $token = $this->loginComoMesero();

        foreach ($invalidDataSets as $dataSet) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->postJson('/api/pedido', $dataSet['data']);

            $response->assertStatus(422)
                ->assertJsonStructure([
                    'message',
                    'errors'
                ]);
        }
    }

    public function test_store_pedido_falla_con_lista_platillos_vacio()
    {
        $token = $this->loginComoMesero();

        // Prueba cuando 'platillos' está vacío
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pedido', [
            'id_mesa' => 1,
            'id_empleado' => 1,
            'platillos' => json_encode([]), // platillos vacío
            'id_restaurante' => 1,
            'tipo' => 'local',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'status',
                'error'
            ]);
    }

    public function test_store_pedido_falla_con_plato_inexistente()
    {
        $token = $this->loginComoMesero();

        // Prueba cuando hay un platillo, se envia una respuesta de error inesperado
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pedido', [
            'id_mesa' => 1, // Simular que esta mesa ya tiene una cuenta abierta
            'id_empleado' => 1,
            'platillos' => json_encode([
                ['id_platillo' => 1, 'cantidad' => 2, 'precio_unitario' => 15],
            ]),
            'id_restaurante' => 1,
            'tipo' => 'local',
        ]);

        $response->assertStatus(500);
    }

    public function test_store_pedido_asignar_pedidos_misma_mesa_misma_cuenta()
    {
        // Crear platillos
        Platillo::factory(3)->asignarMenu(1)->create([
            'id_restaurante' => 1
        ]);

        $token = $this->loginComoMesero();

        // Primer pedido para la mesa 1
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pedido', [
            'id_mesa' => 1,
            'id_empleado' => 1,
            'platillos' => json_encode([
                [
                    'id_platillo' => 2,
                    'cantidad' => 1,
                    'precio_unitario' => 20,
                    'detalle' => ''
                ],
            ]),
            'id_restaurante' => 1,
            'tipo' => 'local',
        ]);
        // $response->dump();
        $response->assertStatus(200);
        $primerPedido = $response->json('pedido');

        // Segundo pedido para la misma mesa (1), debe usar la misma cuenta
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pedido', [
            'id_mesa' => 1,
            'id_empleado' => 1,
            'platillos' => json_encode([
                [
                    'id_platillo' => 3,
                    'cantidad' => 1,
                    'precio_unitario' => 5,
                    'detalle' => ''
                ],
            ]),
            'id_restaurante' => 1,
            'tipo' => 'local',
        ]);

        $response2->assertStatus(200);
        $segundoPedido = $response2->json('pedido');

        // Confirmar que el primer y segundo pedido tienen el mismo id_cuenta
        $this->assertEquals($primerPedido['id_cuenta'], $segundoPedido['id_cuenta']);

        // Tercer pedido para una mesa diferente (2), debe crear una nueva cuenta
        $response3 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pedido', [
            'id_mesa' => 2,
            'id_empleado' => 1,
            'platillos' => json_encode([
                [
                    'id_platillo' => 1,
                    'cantidad' => 2,
                    'precio_unitario' => 15,
                    'detalle' => 'Este platillo es sin sal'
                ],
            ]),
            'id_restaurante' => 1,
            'tipo' => 'llevar',
        ]);

        $response3->assertStatus(200);
        $tercerPedido = $response3->json('pedido');

        // Confirmar que el tercer pedido tiene una cuenta diferente a los anteriores
        $this->assertNotEquals($primerPedido['id_cuenta'], $tercerPedido['id_cuenta']);
    }

    public function test_store_pedido_crea_nueva_cuenta()
    {
        // Crear una cuenta para la mesa 1 con fecha de un día anterior
        $cuenta = Cuenta::create([
            'id_mesa' => 1,
            'nit' => 0,
            'monto_total' => 50, // Puedes establecer cualquier monto
            'estado' => 'Abierta', // Asegúrate de que la cuenta esté abierta
            'id_restaurante' => 1
        ]);
        $cuenta = Cuenta::create([
            'id_mesa' => 1,
            'nit' => 0,
            'monto_total' => 50, // Puedes establecer cualquier monto
            'estado' => 'Abierta', // Asegúrate de que la cuenta esté abierta
            'id_restaurante' => 1
        ]);

        $cuenta->created_at = now()->subDay(); // Fecha de un día anterior
        $cuenta->save();


        // Crear platillos
        Platillo::factory(3)->asignarMenu(1)->create([
            'id_restaurante' => 1
        ]);

        // Datos para el pedido que intentaremos crear
        $data = [
            'id_mesa' => 1, // La mesa que tiene la cuenta abierta
            'id_empleado' => 1,
            'platillos' => json_encode([
                [
                    'id_platillo' => 1,
                    'cantidad' => 1,
                    'precio_unitario' => 15,
                    'detalle' => 'Este platillo es sin sal'
                ],
            ]),
            'id_restaurante' => 1,
            'tipo' => 'local',
        ];

        // Autenticación como empleado
        $token = $this->loginComoMesero();

        // Realizar la petición para crear el pedido
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/pedido', $data);

        // Verificar que la respuesta es un error de cuenta abierta
        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['pedido' => ['id', 'tipo', 'fecha_hora_pedido']]);

        // Verificar que no se haya creado un nuevo pedido
        $this->assertDatabaseMissing('pedidos', [
            'id_empleado' => 1,
            'id_mesa' => 1,
            'tipo' => 'local',
        ]);
    }
}
