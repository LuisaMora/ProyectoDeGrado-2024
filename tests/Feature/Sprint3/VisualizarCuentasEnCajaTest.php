<?php

namespace Tests\Feature\Sprint3;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Cuenta;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Platillo;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class VisualizarCuentasEnCajaTest extends TestCase
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

        // se crea cajero
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('cajero1', 'cajero1@gmail.com')->create([
                    'tipo_usuario' => 'Empleado' 
                ])->id,
            'id_rol' => 2,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70952261',
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
    }

    private function loginComoCajero(): string
    {
        // Realiza el login del cajero y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'cajero1',
            'password' => '12345678',
        ]);

        return $response['token'];
    }

    public function test_cajero_puede_ver_cuentas_abiertas_por_mesa()
    {
        // Autenticar como cajero
        $token = $this->loginComoCajero();
        // ID del restaurante a verificar
        $idRestaurante = 1;

        // Realizar la solicitud al endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/cuentas/abiertas/{$idRestaurante}");

        // Verificar que la respuesta tiene éxito
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'cuentas' => [
                    '*' => [
                        'id',
                        'id_mesa',
                        'nombre_mesa',
                        'estado',
                        'nombre_razon_social',
                        'monto_total',
                        'nit',
                        'platos' => [
                            '*' => [
                                'id',
                                'nombre',
                                'precio',
                                'id_pedido',
                                'id_platillo',
                                'cantidad'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_cajero_obtiene_una_cuenta_existente()
    {
        // Obtener la cuenta creada
        $cuenta = Cuenta::first();

        // Autenticar como cajero
        $token = $this->loginComoCajero();

        // Realizar la solicitud al endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/show/cuenta/{$cuenta->id}");

        // Verificar que la respuesta tiene éxito y contiene los datos esperados
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'cuenta' => [
                    'id',
                    'id_mesa',
                    'nombre_mesa',
                    'estado',
                    'nombre_razon_social',
                    'monto_total',
                    'nit',
                    'platos' => [
                        '*' => [
                            'id',
                            'nombre',
                            'precio',
                            'id_pedido',
                            'id_platillo',
                            'cantidad'
                        ]
                    ]
                ]
            ]);
    }

    public function test_cajero_obtiene_una_cuenta_error_no_existe()
    {
        // Autenticar como cajero
        $token = $this->loginComoCajero();

        // Realizar la solicitud al endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/show/cuenta/999");

        // Verificar que la respuesta retorna un error 404
        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'error' => 'Cuenta no encontrada.'
            ]);
    }

    public function test_cajero_puede_ver_cuentas_cerradas_por_mesa()
    {
        // Crear una cuenta cerrada (Pagada)
        DB::table('cuentas')->insert([
            [
                'id_mesa' => 1,
                'estado' => 'Pagada',
                'nombre_razon_social' => 'Cliente A',
                'nit' => 12345678,
                'monto_total' => 150.00,
                'created_at' => now(),
                'updated_at' => now(),
                'id_restaurante' => 1
            ],
        ]);

        // Autenticar como cajero
        $token = $this->loginComoCajero();

        // ID del restaurante a verificar
        $idRestaurante = 1;

        // Realizar la solicitud al endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/cuentas/cerradas/{$idRestaurante}");

        // Verificar que la respuesta tiene éxito
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'cuentas' => [
                    '*' => [
                        'id',
                        'id_mesa',
                        'nombre_mesa',
                        'estado',
                        'nombre_razon_social',
                        'monto_total',
                        'nit',
                        'platos' => [
                            '*' => [
                                'id',
                                'nombre',
                                'precio',
                                'id_pedido',
                                'id_platillo',
                                'cantidad'
                            ]
                        ]
                    ]
                ]
            ]);
    }
}
