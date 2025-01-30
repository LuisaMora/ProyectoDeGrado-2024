<?php

namespace Tests\Feature\Sprint2;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Menu;
use App\Models\Propietario;
use App\Models\Restaurante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisualizarCategoriasTest extends TestCase
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
    }

    private function loginComoPropietario($usuario, $password): string
    {
        // Realiza el login del propietario y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => $usuario,
            'password' => $password,
        ]);

        return $response['token'];
    }

    public function test_obtener_categorias_exitosamente()
    {
        $token = $this->loginComoPropietario('propietarioA1', '12345678');
        // Obtener el restaurante creado en setUpDatosIniciales
        $restaurante = Restaurante::first(); 

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/categoriaRestaurante/' . $restaurante->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'categorias' => [
                         ['nombre' => 'Otros'],
                         ['nombre' => 'Desayunos'],
                         ['nombre' => 'Comida'],
                         ['nombre' => 'Cena'],
                         ['nombre' => 'Bebidas'],
                         ['nombre' => 'Postres'],
                     ],
                 ]);
    }

    public function test_no_encontrar_menu_para_restaurante()
    {
        // Probar un restaurante sin menú asociado
        $token = $this->loginComoPropietario('propietarioA1', '12345678');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/categoriaRestaurante/9999'); // ID que no existe

        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'Menu no encontrado para el restaurante.',
                 ]);
    }

    public function test_no_encontrar_categorias_para_menu()
    {
        // Probar un menú sin categorías
        $token = $this->loginComoPropietario('propietario_2A1', '12345678');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/categoriaRestaurante/' . 2);

        $response->assertStatus(404)
                 ->assertJson([
                     'message' => 'Categorias no encontradas para el menu.',
                 ]);
    }

}
