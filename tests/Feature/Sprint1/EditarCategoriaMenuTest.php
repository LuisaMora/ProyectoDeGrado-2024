<?php

namespace Tests\Feature\Sprint1;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Propietario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditarCategoriaMenuTest extends TestCase
{
    use RefreshDatabase;
    // solo lo qu esta en setup se puede usar en las demas metodos, cada metodo es aislado
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

    public function test_obtener_categoria_para_editar(): void
    {
        // Crear una categoría de ejemplo para el menu 1
        $categoria = Categoria::create([
            'nombre' => 'Bebidas',
            'imagen' => '/storage/categorias/fake_image.jpg',
            'id_menu' => 1
        ]);

        // Realizar login y obtener el token
        $token = $this->loginComoPropietario();

        // Enviar la solicitud para obtener la categoría
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/categoria/' . $categoria->id);

        // Verificar la respuesta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'categoria' => [
                    'id',
                    'nombre',
                    'imagen',
                ],
            ]);
    }

    public function test_obtener_categoria_para_editar_no_encontrado(): void
    {
        // No se agrega categorias
        // Realizar login y obtener el token
        $token = $this->loginComoPropietario();

        // Enviar la solicitud para obtener la categoría
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/categoria/45');

        // Verificar la respuesta
        $response->assertStatus(404)
            ->assertJson(['message' => 'Categoria no encontrada.']);
    }

    public function test_editar_categoria_exitosamente(): void
    {
        // ya existe una categoria, es la por defecto, entonces agregaremos otra
        $categoria = Categoria::create([
            'nombre' => 'Postres',
            'imagen' => '/storage/categorias/original_image.jpg',
            'id_menu' => 1
        ]);
        $nuevaImagen = UploadedFile::fake()->image('postre_actualizada.jpg');
        $token = $this->loginComoPropietario();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/categoria/' . $categoria->id, [
            'nombre' => 'Postre Actualizado',
            'imagen' => $nuevaImagen,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'categoria' => [
                    'id',
                    'nombre',
                    'imagen',
                ],
            ]);

        // Verificación en la base de datos
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'nombre' => 'Postre Actualizado',
        ]);

    }

    public function test_editar_categoria_no_encontrada(): void
    {
        $nuevaImagen = UploadedFile::fake()->image('inexistente.jpg');
        $token = $this->loginComoPropietario();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/categoria/999', [
            'nombre' => 'Categoría Inexistente',
            'imagen' => $nuevaImagen,
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Categoria no encontrada.']);
    }

    public function test_editar_categoria_por_defecto_error(): void
    {
        // la categoria 1 es la por defecto
        $token = $this->loginComoPropietario();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/categoria/1', [
            'nombre' => 'Categoría Inexistente',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'No se puede editar la categoria por defecto.']);
    }

    public function test_editar_categoria_sin_cambiar_imagen(): void
    {
        $categoria = Categoria::create([
            'nombre' => 'Bebidas',
            'imagen' => '/storage/categorias/fake_image.jpg',
            'id_menu' => 1
        ]);
        $token = $this->loginComoPropietario();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/categoria/' . $categoria->id, [
            'nombre' => 'Bebidas Sin Imagen Cambiada',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'categoria' => [
                    'id' => $categoria->id,
                    'nombre' => 'Bebidas Sin Imagen Cambiada',
                    'imagen' => $categoria->imagen,
                ],
            ]);

    }
}
