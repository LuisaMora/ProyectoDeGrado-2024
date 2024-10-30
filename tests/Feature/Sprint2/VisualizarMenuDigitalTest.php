<?php

namespace Tests\Feature\Sprint2;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Platillo;
use App\Models\Propietario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisualizarMenuDigitalTest extends TestCase
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

        // Creación de usuarios
        Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        Propietario::factory()->asignarDatosSesion('propietarioA1', 'propietario@gmail.com')->create();
        Propietario::factory()->asignarDatosSesion('propietario_2A1', 'propietario2@gmail.com')->create();
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

        // plato eliminado
        Platillo::factory()->create([
            'id_menu' => 1,
            'disponible' => false,
            'plato_disponible_menu' => true,
        ]);
        // plato no habilitado en el manu
        Platillo::factory()->create([
            'id_menu' => 1,
            'disponible' => true,
            'plato_disponible_menu' => false,
        ]);
        Platillo::factory(8)->asignarMenu(1)->create();
    }

    public function test_mostrar_menu_correctamente()
    {
        // El menu con id 1 existe con 10 platillos 8 validos 2 no validos
        // Hacer la solicitud GET al menú
        $response = $this->getJson('/api/menu/' . 1);

        // Verificar que la respuesta es correcta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'menu',
                'platillos',
                'nombre_restaurante'
            ]);

        // Total de platillos disponibles (8 de factory - 2 no disponibles)
        $this->assertCount(8, $response->json('platillos'));
    }

    public function test_mostrar_menu_no_existente()
    {
        // Hacer la solicitud GET a un menú que no existe
        $response = $this->getJson('api/menu/999'); // 999 es un ID que no existe

        // Verificar que se recibe un error 404
        $response->assertStatus(404)
            ->assertJson(['message' => 'Menu no encontrado.']);
    }

}
