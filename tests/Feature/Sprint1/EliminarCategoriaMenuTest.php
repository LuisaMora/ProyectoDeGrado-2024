<?php

namespace Tests\Feature\Sprint1;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Platillo;
use App\Models\Propietario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EliminarCategoriaMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatosIniciales();
        Storage::fake('public'); // Simular el sistema de archivos
    }

    private function setUpDatosIniciales(): void
    {
        // Inserción de roles y usuarios
        Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        Propietario::factory()->asignarDatosSesion('propietarioA1', 'propietario@gmail.com')->create();

        // Creación de una categoría
        Categoria::factory()->create([
            'nombre' => 'Bebidas',
            'imagen' => '/storage/categorias/original_image.jpg',
        ]);
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

    public function test_eliminar_categoria_por_defecto(): void
    {
        // Usar categoria creada con id 1
 

        // Obtener el token de autenticación
        $token = $this->loginComoPropietario();

        // Intentar eliminar la categoría por defecto
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/menu/categoria/1');

        // Verificar que no se pueda eliminar y que el mensaje sea el correcto
        $response->assertStatus(400)
            ->assertJson([
                'message' => 'No se puede eliminar la categoria por defecto.',
            ]);
    }

    public function test_eliminar_categoria_no_existente(): void
    {
        // Obtener el token de autenticación
        $token = $this->loginComoPropietario();

        // Intentar eliminar una categoría inexistente
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/menu/categoria/999'); // ID inexistente

        // Verificar que se obtenga un error 404 y el mensaje esperado
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Categoria no encontrada.',
            ]);
    }

    public function test_eliminar_categoria_exitosamente(): void
    {
        // Crear una categoría adicional
        $categoria = Categoria::create([
            'nombre' => 'Postres',
            'estado' => true,
            'id_menu' => 1
        ]);

        // Crear un platillo asignado a esta categoría
        $platillo = Platillo::create([
            'id_menu' => 1,
            'nombre' => 'Tiramisu',
            'descripcion' => 'Delicioso postre italiano.',
            'precio' => 40,
            'id_categoria' => $categoria->id,
            'imagen' => '/storage/platillos/tiramisu.jpg',
            'disponible' => true,
            'plato_disponible_menu' => true,
            'id_restaurante' => 1,
        ]);

        // Obtener el token de autenticación
        $token = $this->loginComoPropietario();

        // Enviar la solicitud para eliminar la categoría
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/menu/categoria/' . $categoria->id);

        // Verificar que la categoría se elimine y que el mensaje sea correcto
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Categoria eliminada.',
            ]);

        // Comprobar que la categoría tenga el estado 'false' en la base de datos
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'estado' => false,
        ]);

        // Comprobar que el platillo haya sido reasignado a la categoría por defecto (ID 1)
        $this->assertDatabaseHas('platillos', [
            'id' => $platillo->id,
            'id_categoria' => 1,
        ]);
    }
}
