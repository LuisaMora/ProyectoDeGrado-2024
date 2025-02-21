<?php

namespace Tests\Feature\S1;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Platillo;
use App\Models\Propietario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditarMenuDigitalTest extends TestCase
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
        // Propietario::factory()->asignarDatosSesion('propietario_2A1', 'propietario2@gmail.com')->create();
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
        //en total 9 platillos disponibles para editar
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

    public function test_mostrar_menu_correctamente_antes_de_editar()
    {
        // Realizar la solicitud GET al menú
        $token = $this->loginComoPropietario();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/menu/datos/1');

        // Verificar la respuesta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'menu',
                'platillos',
            ]);

        // Asegurarse de que los platillos disponibles sean correctos, 8 disponilbes en menu y 1 no
        $this->assertCount(9, $response->json('platillos'));
    }

    public function test_editar_menu_exitosamente()
    {
        $imagenFalsa = UploadedFile::fake()->image('portada.jpg');

        $token = $this->loginComoPropietario();
        $platillos = Platillo::where('id_menu', 1)->pluck('id')->toArray(); // Obtén IDs de los platillos

        // Enviar la solicitud para editar el menú
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson('/api/menu', [
            'id_menu' => 1,
            'tema' => 'Nuevo Tema',
            'platillos' => json_encode(array_map(function ($id) {
                return ['id' => $id, 'plato_disponible_menu' => true];
            }, $platillos)),
            'portada' => $imagenFalsa,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Verificar que el menú se haya actualizado correctamente
        $this->assertDatabaseHas('menus', [
            'id' => 1,
            'tema' => 'Nuevo Tema',
        ]);

        // Verificar que los platillos tengan el nuevo estado
        foreach ($platillos as $platilloId) {
            $this->assertDatabaseHas('platillos', [
                'id' => $platilloId,
                'plato_disponible_menu' => true,
            ]);
        }
    }

    public function test_editar_menu_exitosamente_ningun_plato_disponible()
    {
        $token = $this->loginComoPropietario();
        $platillos = Platillo::where('id_menu', 1)->pluck('id')->toArray(); // Obtén IDs de los platillos

        // Enviar la solicitud para editar el menú sin imagen
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson('/api/menu', [
            'id_menu' => 1,
            'tema' => 'Nuevo Tema Sin Imagen',
            'platillos' => json_encode(array_map(function ($id) {
                return ['id' => $id, 'plato_disponible_menu' => false];
            }, $platillos)),
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);

        // Verificar que el tema del menú se haya actualizado correctamente
        $this->assertDatabaseHas('menus', [
            'id' => 1,
            'tema' => 'Nuevo Tema Sin Imagen',
        ]);

        // Verificar que los platillos tengan el nuevo estado (plato_disponible_menu = false)
        foreach ($platillos as $platilloId) {
            $this->assertDatabaseHas('platillos', [
                'id' => $platilloId,
                'plato_disponible_menu' => false,
            ]);
        }
    }


    public function test_editar_menu_datos_invalidos()
    {
        $token = $this->loginComoPropietario();

        // Enviar la solicitud con datos inválidos
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson('/api/menu', [
            'id_menu' => 'no_es_un_numero',
            'tema' => 'T', // Demasiado corto
            'platillos' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_editar_menu_no_encontrado()
    {
        $token = $this->loginComoPropietario();

        // Intentar editar un menú que no existe
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson('/api/menu', [
            'id_menu' => 999, // ID que no existe
            'tema' => 'Tema',
            'platillos' => json_encode([]),
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Menu no encontrado.']);
    }
}
