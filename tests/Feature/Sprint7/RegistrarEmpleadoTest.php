<?php

namespace Tests\Feature\Sprint7;

use App\Mail\RegistroEmpleado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;


class RegistrarEmpleadoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatosIniciales();
        Storage::fake('public');
        Mail::fake();
    }

    private function setUpDatosIniciales(): void
    {
        // Inserción de roles
        DB::table('rol_empleados')->insert([
            ['nombre' => 'Mesero'],
            ['nombre' => 'Cajero'],
            ['nombre' => 'Cocinero'],
        ]);

        // Insertar los estados del pedido
        $estados = [
            ['nombre' => 'En espera'],
            ['nombre' => 'En preparación'],
            ['nombre' => 'Listo para servir'],
            ['nombre' => 'Servido'],
            ['nombre' => 'Cancelado'],
        ];

        foreach ($estados as $estado) {
            EstadoPedido::create($estado);
        }
        // Creación de usuarios
        Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();
        Propietario::factory()->asignarDatosSesion('propietarioA1', 'propietario@gmail.com')->create();

        // Inserción de mesas
        Mesa::factory(2)->registrar_a_restaurante(1)->create();
   
    }

    private function loginComoPropietario()
    {
        // Realiza el login del propietario y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'propietarioA1',
            'password' => '12345678',
        ]);

        return $response['token'];
    }

    public function test_registro_exitoso_empleado()
    {
        // Loguearse como propietario
        $token = $this->loginComoPropietario();
        $imagenFalsa = UploadedFile::fake()->image('foto.jpg');

        // Datos para registrar empleado
        $data = [
            'nombre' => 'Juan',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Gomez',
            'correo' => 'juan.perez@gmail.com',
            'nickname' => 'jperez',
            'id_rol' => 1, // Rol: Mesero
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => '2023-11-01',
            'ci' => '12345678',
            'direccion' => 'Calle Falsa 123',
            'foto_perfil' => $imagenFalsa
        ];

        // Llamar a la API para registrar al empleado
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/empleado', $data);

        // Aserciones de respuesta exitosa y estructura de JSON
        $response->dump($response->json());
        $response->assertStatus(201)
            ->assertJson(['status' => 'success']);

        // Aserción de que el usuario fue guardado en la base de datos
        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Juan',
            'apellido_paterno' => 'Perez',
            'correo' => 'juan.perez@gmail.com'
        ]);

        // Aserción de que el empleado fue guardado en la base de datos
        $this->assertDatabaseHas('empleados', [
            'ci' => '12345678',
            'direccion' => 'Calle Falsa 123'
        ]);

        // Verificar que el correo de registro fue enviado al usuario
        Mail::assertSent(RegistroEmpleado::class, function ($mail) use ($data) {
            return $mail->hasTo($data['correo']);
        });
    }

    public function test_registro_fallo_creacion_usuario()
    {
        // Loguearse como propietario
        $token = $this->loginComoPropietario();

        // Simulamos fallo en el guardado del usuario
        User::creating(function () {
            throw new \Exception('Simulated failure in user creation');
        });

        // Datos de prueba
        $data = [
            'nombre' => 'Maria',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Ramirez',
            'correo' => 'maria.lopez@gmail.com',
            'nickname' => 'mlopez',
            'id_rol' => 1,
            'fecha_nacimiento' => '1990-02-02',
            'fecha_contratacion' => '2023-10-15',
            'ci' => '87654321',
            'direccion' => 'Avenida Siempre Viva',
            'foto_perfil' => UploadedFile::fake()->image('foto_maria.jpg')
        ];

        // Llamar a la API
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/empleado', $data);

        // Aserción de que la respuesta es un error
        $response->assertStatus(500)
            ->assertJson(['status' => 'error']);

        // Verificar que no se guardaron registros en 'usuarios' ni en 'empleados'
        $this->assertDatabaseMissing('usuarios', ['correo' => 'maria.lopez@gmail.com']);
        $this->assertDatabaseMissing('empleados', ['ci' => '87654321']);
    }

    public function test_registro_fallo_envio_correo()
    {
        // Loguearse como propietario
        $token = $this->loginComoPropietario();

        // Configurar Mail para lanzar excepción en el envío
        Mail::shouldReceive('to->send')->andThrow(new \Exception('Simulated email failure'));

        // Datos de prueba
        $data = [
            'nombre' => 'Carlos',
            'apellido_paterno' => 'Martinez',
            'apellido_materno' => 'Reyes',
            'correo' => 'carlos.martinez@gmail.com',
            'nickname' => 'cmartinez',
            'id_rol' => 2,
            'fecha_nacimiento' => '1995-03-03',
            'fecha_contratacion' => '2023-09-20',
            'ci' => '23456789',
            'direccion' => 'Calle Luna',
            'foto_perfil' => UploadedFile::fake()->image('foto_carlos.jpg')
        ];

        // Llamar a la API
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/empleado', $data);

        // Aserción de que la respuesta es un error
        $response->assertStatus(500)
            ->assertJson(['status' => 'error', 'message' => 'Simulated email failure']);

        // Verificar que se hizo rollback en las tablas 'usuarios' y 'empleados'
        $this->assertDatabaseMissing('usuarios', ['correo' => 'carlos.martinez@gmail.com']);
        $this->assertDatabaseMissing('empleados', ['ci' => '23456789']);
    }

}
