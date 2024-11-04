<?php

namespace Tests\Feature\Sprint1;

use App\Models\Administrador;
use App\Models\Empleado;
use App\Models\Propietario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrarPlatilloRestauranteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Inserción de roles y categorías
        DB::table('rol_empleados')->insert([
            ['nombre' => 'Mesero'],
            ['nombre' => 'Cajero'],
            ['nombre' => 'Cocinero'],
        ]);

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
            \App\Models\Categoria::factory()->create(['nombre' => $categoria]);
        }

        Empleado::create([
            'id_usuario' => \App\Models\User::factory()
                ->asignarNicknameCorreo('empleado1', 'empleado1@gmail.com')->create()->id,
            'id_rol' => 1,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951529',
            'direccion' => 'Cochabamba',
        ]);
        // Simular el sistema de archivos para pruebas
        Storage::fake('public');
    }

    public function test_registrar_platillo_exitosamente(): void
    {
        // Configuramos lo necesario para registrar el platillo, preparamos los datos
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);
        $token = $responsePropietario['token'];
        $imagenFalsa = UploadedFile::fake()->image('pique_macho.jpg');

        // Realizamos la solicitud para el registro el platillo
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/platillo', [
            'nombre' => 'Pique Macho',
            'descripcion' => 'Pique grande con porciones de papa descomunales.',
            'precio' => 80,
            'id_categoria' => 2,
            'id_restaurante' => 1,
            'imagen' => $imagenFalsa,
        ]);

        // verificamos que la respuesta de la API 201 creado
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'platillo' => [
                    'nombre',
                    'descripcion',
                    'precio',
                    'id_categoria',
                    'imagen',
                ],
            ]);

        // verificamos que el platillo se guarda en la base de datos
        $this->assertDatabaseHas('platillos', [
            'id' => $response->json('platillo.id'),
        ]);
    }

    public function test_error_registrar_platillo_datos_invalidos(): void
    {
        // Configuramos lo necesario
        Storage::fake('public');
        $responsePropietario = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);
        $token = $responsePropietario['token'];

        // Realizamos la solicitud con datos inválidos
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/platillo', [
            'nombre' => '', // nombre vacío
            'descripcion' => 'Pique grande con porciones de papa descomunales.',
            'precio' => 'invalid_price', // precio inválido
            // 'id_categoria' => 2, // id_categoria ausente
            'id_restaurante' => 1,
            'imagen' => null, // imagen no proporcionada
        ]);

        // Verificamos que la respuesta es 422 y contiene errores de validación
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'nombre',
                    'precio',
                    'imagen',
                    // Puedes agregar más campos según sea necesario
                ],
            ]);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/menu/platillo', [
            'nombre' => '', // nombre vacío
            'descripcion' => 'Pique grande con porciones de papa descomunales.',
            'precio' => 'invalid_price', // precio inválido
            'id_categoria' => 2,
            // 'id_restaurante' => 1, // id_restaurante ausente
            'imagen' => null, // imagen no proporcionada
        ]);

        $response2->assertStatus(422);
    }
}
