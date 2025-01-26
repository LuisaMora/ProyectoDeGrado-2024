<?php

namespace Tests\Feature\Sprint1;

use App\Models\Administrador;
use App\Models\Propietario;
use App\Models\Restaurante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrarCategoriaMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatosIniciales();
        Storage::fake('public'); // Simulación del sistema de archivos para las pruebas
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

    public function test_crear_categoria_exitosamente(): void
    {
        $token = $this->loginComoPropietario(); // Usar el método auxiliar para obtener el token
        $imagenFalsa = UploadedFile::fake()->image('categoria.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/categoria/', [
            'id_restaurante' => 1,
            'nombre' => 'Entradas',
            'imagen' => $imagenFalsa,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'categoria' => [
                    'id',
                    'nombre',
                    'imagen',
                    'id_menu',
                ],
            ]);

        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Entradas',
            'id_menu' => 1,
        ]);
    }

    public function test_falla_creacion_con_datos_invalidos(): void
    {
        $token = $this->loginComoPropietario(); // Reutilización del login
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/categoria/', [
            'id_restaurante' => 1,
        ]);

        $response->assertStatus(422)
        ->assertJsonFragment([
            'status' => 'error',
        ])
        ->assertJsonValidationErrors([
            'nombre',
            'imagen',
        ]);
    }

    public function test_falla_si_restaurante_no_existe(): void
    {
        $token = $this->loginComoPropietario(); // Reutilización del login
        $imagenFalsa = UploadedFile::fake()->image('categoria.jpg');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/categoria', [
            'id_restaurante' => 999,
            'nombre' => 'Plato Fuerte',
            'imagen' => $imagenFalsa,
        ]);

        $response->assertStatus(404)
            ->assertJsonFragment([
                'status' => 'error',
                'message' => 'Restaurante no encontrado',
            ]);
    }

    public function test_falla_al_guardar_imagen(): void
    {
        $token = $this->loginComoPropietario(); // Reutilización del login
        $imagenInvalida = UploadedFile::fake()->create('archivo.txt', 100);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/categoria', [
            'id_restaurante' => 1,
            'nombre' => 'Postres',
            'imagen' => $imagenInvalida,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'status' => 'error',
            ])
            ->assertJsonStructure([
                'errors' => [
                    'imagen',
                ],
            ]);
    }
}
