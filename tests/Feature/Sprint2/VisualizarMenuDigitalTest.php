<?php

namespace Tests\Feature\Sprint2;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Platillo;
use App\Models\Propietario;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisualizarMenuDigitalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public'); // Simular el sistema de archivos
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
            'id_restaurante' => 1
        ]);
        // plato no habilitado en el manu
        Platillo::factory()->create([
            'id_menu' => 1,
            'disponible' => true,
            'plato_disponible_menu' => false,
            'id_restaurante' => 1
        ]);
        Platillo::factory(8)->asignarMenu(1)->create([
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
                'platillos'
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
            ->assertJson(['error' => 'Menu no encontrado.']);
    }

    public function test_generar_qr_exitosamente()
    {
        $token = $this->loginComoPropietario();

        $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/menu/generar/qr', [
            'direccion_url_menu' => 'https://example.com/menu'
        ]);

        // Verificar que se creó el archivo QR
        $this->assertTrue(File::exists(storage_path('app/public/codigos_qr')));

        // Verificar que el estado sea exitoso y la URL del QR esté en la respuesta
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'qr']);
    }

    public function test_generar_qr_con_url_invalida()
    {
        $token = $this->loginComoPropietario();

        $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/menu/generar/qr', [
            'direccion_url_menu' => 'no-es-una-url'
        ]);

        // Verificar que se retorne un error de validación
        $response->assertStatus(422)
            ->assertJsonStructure([ 'errors']);
    }



}
