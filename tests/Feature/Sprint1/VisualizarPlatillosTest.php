<?php

namespace Tests\Feature\Sprint1;

use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\Platillo;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisualizarPlatillosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Inserción de un restaurante con un menú y categorías de prueba
        \Illuminate\Support\Facades\DB::table('rol_empleados')->insert([
            ['nombre' => 'Mesero'],
            ['nombre' => 'Cajero'],
            ['nombre' => 'Cocinero'],
        ]);

        // Creación de usuario para el restaurante 1
        \App\Models\Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        \App\Models\Propietario::factory()
            ->asignarDatosSesion('propietarioA1', 'propietario@gmail.com')
            ->create();

        $categorias = [
            'Otros',
            'Desayunos',
            'Comida',
            'Cena',
            'Bebidas',
            'Postres',
        ];
        foreach ($categorias as $categoria) {
            Categoria::factory()->create(['nombre' => $categoria]);
        }

        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('empleado1', 'empleado1@gmail.com')->create()->id,
            'id_rol' => 1,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951529',
            'direccion' => 'Cochabamba',
            'id_restaurante' => 1
        ]);

        // Creación de usuario para el restaurante 2
        Propietario::factory()
            ->asignarDatosSesion('propietarioA2', 'propietario2@gmail.com')
            ->create();
        // Crear categorías para los platillos
        Categoria::create([
            'nombre' => 'Entradas',
            'id_menu' => 2,
            'imagen' => '/img.jpg',
            'estado' => true,
        ]);
        Categoria::create([
            'nombre' => 'Plato Fuerte',
            'id_menu' => 2,
            'imagen' => '/img.jpg',
            'estado' => true,
        ]);

        // Crear 2 platillos en la base de datosasociados al menú del restaurante
        Platillo::factory(2)->asignarMenu(1)->create([
            'id_restaurante' => 1
        ]);
    }

    /** @test */
    public function test_obtener_platillos_disponibles_del_restaurante_exitosamente(): void
    {
        //realizamos el inicio de sesion
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);
        $token = $responsePropietario['token'];

        $restaurante = 1;
        // Realizamos la solicitud para obtener los platillos
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/platillos/' . $restaurante);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'platillos' => [
                    '*' => [
                        'id',
                        'nombre',
                        'id_categoria',
                        'disponible',
                    ],
                ],
            ]);

        // Verificamos que los platillos pertenecen al menú del restaurante y están disponibles
        $this->assertCount(2, $response->json('platillos'));
    }

    public function test_obtener_platillos_de_otro_restaurante_sin_resultados(): void
    {
        $otroRestaurante = 2;
        //realizamos el inicio de sesion
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);
        $token = $responsePropietario['token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/platillos/' . $otroRestaurante);

        // Verificamos que no hay platillos disponibles para el restaurante si respuesta 200
        $this->assertEmpty($response->json('platillos'));
    }
}
