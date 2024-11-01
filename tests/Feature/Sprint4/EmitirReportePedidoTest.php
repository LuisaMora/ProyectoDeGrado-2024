<?php

namespace Tests\Feature\Sprint4;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Platillo;
use App\Models\Propietario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EmitirReportePedidoTest extends TestCase
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
        // Creación de 20 cuentas con estado 'Pagada' y fechas aleatorias en los últimos 7 días
        $cuentas = [];
        for ($i = 1; $i <= 20; $i++) {
            $fechaAleatoria = now()->subDays(rand(0, 7));
            $cuentas[] = [
                'id_mesa' => ($i % 2) + 1, // Alterna entre las mesas 1 y 2
                'estado' => 'Pagada',
                'nombre_razon_social' => 'Cliente ' . chr(64 + $i), // Cliente A, B, etc.
                'nit' => 10000000 + $i,
                'monto_total' => rand(50, 200), // Monto aleatorio
                'created_at' => $fechaAleatoria,
                'updated_at' => $fechaAleatoria,
            ];
        }
        DB::table('cuentas')->insert($cuentas);

        // Creación de pedidos para cada cuenta
        $pedidoData = [];
        foreach (DB::table('cuentas')->get() as $cuenta) {
            $numPedidos = rand(1, 2); // Uno o dos pedidos por cuenta
            for ($j = 0; $j < $numPedidos; $j++) {
                $pedidoData[] = [
                    'id_cuenta' => $cuenta->id,
                    'tipo' => ['local', 'llevar'][rand(0, 1)], // Tipo aleatorio
                    'id_estado' => rand(1, 4), // Estado aleatorio entre 'En espera' y 'Servido'
                    'id_empleado' => 1,
                    'fecha_hora_pedido' => $cuenta->created_at,
                    'monto' => $cuenta->monto_total / $numPedidos, // Distribuir el monto de la cuenta
                    'created_at' => $cuenta->created_at,
                    'updated_at' => $cuenta->updated_at,
                ];
            }
        }
        DB::table('pedidos')->insert($pedidoData);

        // Obtener platillos aleatorios y asociarlos a cada pedido
        $platillos = DB::table('platillos')->inRandomOrder()->take(3)->get(); // Tres platillos aleatorios
        foreach (DB::table('pedidos')->get() as $pedido) {
            foreach ($platillos as $platillo) {
                DB::table('plato_pedido')->insert([
                    'id_pedido' => $pedido->id,
                    'id_platillo' => $platillo->id,
                    'detalle' => 'Detalles adicionales aquí',
                    'cantidad' => rand(1, 3), // Cantidad aleatoria
                    'precio_fijado' => $platillo->precio,
                    'created_at' => $pedido->created_at,
                    'updated_at' => $pedido->updated_at,
                ]);
            }
        }
    }

    private function loginComoPropietario(): string
    {
        // Realiza el login del propietario y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);

        return $response['token'];
    }

    public function test_get_reporte_hace_siete_dias()
    {
        // Crear un usuario y obtener el token de autenticación

        $token = $this->loginComoPropietario();
        // Definir las fechas de prueba (hace 7 días hasta hoy)
        $fechaInicio = Carbon::now()->subDays(7)->format('Y-m-d');
        $fechaFin = Carbon::now()->format('Y-m-d');

        // Enviar la solicitud a la ruta de reporte con el token de autenticación y parámetros necesarios
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ])->postJson('api/reporte/pedidos', [
            'id_restaurante' => 1,  // Cambia este ID por el que corresponda a tu restaurante de prueba
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);

        // Verificar que la respuesta sea exitosa
        // $response->dump();
        $response->assertStatus(200);

        // Comprobar que la estructura de datos en la respuesta es la esperada
        $response->assertJsonStructure([
            'status',
            'montoTotalPedidosPorDia' => [
                '*' => ['fecha', 'monto']
            ],
            'cantidadPedidosPorDia' => [
                '*' => ['fecha', 'cantidad']
            ],
            'cantidadPedidosPorMesa' => [
                '*' => ['mesa', 'cantidad_pedidos']
            ],
            'cuentas' => [
                '*' => ['id']
            ],
            'pedidoPorCuenta' => [
                '*' => [
                    '*' => [
                        'empleado' => ['nombre', 'apellido'],
                        'monto',
                        'fecha_hora_cuenta',
                        'estado_pedido',
                        'platillos' => [
                            '*' => ['id_platillo', 'nombre', 'precio', 'cantidad', 'detalle']
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function test_get_reporte_id_restaurante_invalido()
    {
        // Login como propietario y obtener el token
        $token = $this->loginComoPropietario();

        // Enviar la solicitud con un ID de restaurante inválido
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ])->postJson('api/reporte/pedidos', [
            'id_restaurante' => 'abc',  // ID inválido
            'fecha_inicio' => null,
            'fecha_fin' => null,
        ]);

        // Verificar que la respuesta sea un error
        $response->assertStatus(400);
        $response->assertJsonStructure(['status', 'error']);
        $this->assertArrayHasKey('id_restaurante', $response->json('error'));
    }

    public function test_get_reporte_fechas_invalidas()
    {
        // Login como propietario y obtener el token
        $token = $this->loginComoPropietario();

        // Enviar la solicitud con fechas inválidas
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ])->postJson('api/reporte/pedidos', [
            'id_restaurante' => 1,
            'fecha_inicio' => 'fecha_invalida',  // Fecha inválida
            'fecha_fin' => 'otra_fecha_invalida',
        ]);

        // Verificar que la respuesta sea un error
        $response->assertStatus(400);
        $response->assertJsonStructure(['status', 'error']);
        $this->assertArrayHasKey('fecha_inicio', $response->json('error'));
        $this->assertArrayHasKey('fecha_fin', $response->json('error'));
    }

    public function test_get_reporte_sin_fechas()
    {
        // Login como propietario y obtener el token
        $token = $this->loginComoPropietario();

        // Enviar la solicitud sin fechas
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json'
        ])->postJson('api/reporte/pedidos', [
            'id_restaurante' => 1,
        ]);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);

        // Comprobar que la respuesta contiene datos y que se corresponde con el rango de fechas por defecto
        $montoTotal = $response->json('montoTotalPedidosPorDia');
        $cantidadPedidos = $response->json('cantidadPedidosPorDia');

        // Verificar que el monto total y la cantidad de pedidos coinciden con lo que se espera
        // (puedes agregar más lógica aquí para verificar si los datos son correctos)
        $this->assertNotEmpty($montoTotal);
        $this->assertNotEmpty($cantidadPedidos);
    }
}
