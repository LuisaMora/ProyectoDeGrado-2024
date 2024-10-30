<?php

namespace Tests\Feature\Sprint1;

use App\Models\Administrador;
use App\Models\Categoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Platillo;
use App\Models\Propietario;

class EliminarPlatilloTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Creación de usuarios
        Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        Propietario::factory()->asignarDatosSesion('propietarioA1', 'propietario@gmail.com')->create();

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

        // Simular el sistema de archivos para pruebas
        Storage::fake('public');
    }

    /** @test */
    public function test_eliminar_platillo_exitosamente()
    {
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);
        $token = $responsePropietario['token'];

        // Crea un platillo de prueba
        $platillo = Platillo::create([
            'id_menu' => 1,
            'nombre' => 'Pique Macho',
            'descripcion' => 'Pique grande con porciones de papa descomunales.',
            'precio' => 80,
            'id_categoria' => 2,
            'imagen' => '/storage/platillos/original_image.jpg',
            'disponible' => true,
            'plato_disponible_menu' => true,
            'id_menu' => 1,
        ]);
        // Realizar la solicitud DELETE para eliminar el platillo existente
        $response =  $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/menu/platillo/{$platillo->id}");

        // Verificar respuesta exitosa
        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Asegura que el platillo esté marcado como no disponible
        $this->assertDatabaseHas('platillos', [
            'id' => $platillo->id,
            'disponible' => false,
        ]);
    }

    /** @test */
    public function test_eliminar_platillo_no_existente()
    {
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);
        $token = $responsePropietario['token'];

        // Realizar la solicitud DELETE para un ID inexistente
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/menu/platillo/9999');

        // Verificar respuesta de error 404 y mensaje específico
        $response->assertStatus(404)
            ->assertJson(['message' => 'Platillo no encontrado.']);
    }
}
