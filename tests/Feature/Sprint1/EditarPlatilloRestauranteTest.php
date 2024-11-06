<?php

namespace Tests\Feature\Sprint1;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\Platillo;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditarPlatilloRestauranteTest extends TestCase
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
        // Inserción de roles
        DB::table('rol_empleados')->insert([
            ['nombre' => 'Mesero'],
            ['nombre' => 'Cajero'],
            ['nombre' => 'Cocinero'],
        ]);

        // Creación de usuarios
        Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        Propietario::factory()->asignarDatosSesion('propietarioA1', 'propietario@gmail.com')->create();

        // Inserción de categorías
        $categorias = ['Otros', 'Desayunos', 'Comida', 'Cena', 'Bebidas', 'Postres'];
        foreach ($categorias as $categoria) {
            Categoria::factory()->create(['nombre' => $categoria]);
        }

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
            'id_restaurante' => 1
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

    public function test_obtener_platillo_para_editar(): void
    {
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
            'id_restaurante' => 1
        ]);

        $token = $this->loginComoPropietario();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/platillo/' . $platillo->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'platillo' => [
                    'id',
                    'nombre',
                    'id_categoria',
                    'disponible',
                ],
            ]);
    }

    public function test_obtener_platillo_para_editar_no_encontrado(): void
    {
        $token = $this->loginComoPropietario();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/platillo/1'); // ID de platillo que no existe

        $response->assertStatus(404);
    }

    public function test_actualizar_platillo_exitosamente(): void
    {
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
            'id_restaurante' => 1
        ]);

        $imagenFalsa = UploadedFile::fake()->image('pique_macho_actualizado.jpg');
        $token = $this->loginComoPropietario();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/platillo/' . $platillo->id, [
            'nombre' => 'Pique Macho Actualizado',
            'descripcion' => 'Descripción actualizada.',
            'precio' => 90,
            'id_categoria' => 2,
            'imagen' => $imagenFalsa, // nueva imagen
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'platillo' => [
                    'id',
                    'nombre',
                    'descripcion',
                    'precio',
                    'id_categoria',
                    'imagen',
                ],
            ]);

        $this->assertDatabaseHas('platillos', [
            'id' => $platillo->id,
            'nombre' => 'Pique Macho Actualizado',
            'descripcion' => 'Descripción actualizada.',
            'precio' => 90,
        ]);
    }

    public function test_actualizar_platillo_no_encontrado(): void
    {
        $imagenFalsa = UploadedFile::fake()->image('imagen.jpg');
        $token = $this->loginComoPropietario();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/platillo/999', [ // ID de platillo que no existe
            'nombre' => 'Platillo Inexistente',
            'descripcion' => 'Descripción.',
            'precio' => 50,
            'id_categoria' => 1,
            'imagen' => $imagenFalsa,
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Platillo no encontrado.']);
    }
}
