<?php

namespace Tests\Feature\S3\GestionarPerfileUsuario;

use App\Mail\AltaUsuario;
use App\Mail\BajaUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CuentaEmpleadoAltaOBajaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatosIniciales();
        Mail::fake();
        // Storage::fake('public'); // Simular el sistema de archivos
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

        // Creación de empleado mesero asociado al propietario
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('empleado1', 'empleado1@gmail.com')->create([
                    'tipo_usuario' => 'Empleado'
                ]
                )->id,
            'id_rol' => 1,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1999-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951529',
            'direccion' => 'Cochabamba',
            'id_restaurante' => 1
        ]);
        // se crea cajero
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('cajero1', 'cajero1@gmail.com')->create([
                    'tipo_usuario' => 'Empleado'
                ])->id,
            'id_rol' => 2,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1999-01-01',
            'fecha_contratacion' => now(),
            'ci' => '153351529',
            'direccion' => 'Cochabamba',
            'id_restaurante' => 1
        ]);
        // se crea cocinero
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('cocinero1', 'cocinero1@gmail.com')->create([
                    'tipo_usuario' => 'Empleado'
                ])->id,
            'id_rol' => 3,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1999-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951561',
            'direccion' => 'Cochabamba',
            'id_restaurante' => 1
        ]);
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

    public function test_dar_de_baja_empleado()
    {
        $token = $this->loginComoPropietario();
        $empleado = Empleado::first();
        $this->assertTrue($empleado->usuario->estado == '1');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/empleado/dar-baja/{$empleado->id_usuario}");

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Empleado dado de baja']);

        $this->assertDatabaseHas('usuarios', [
            'id' => $empleado->id_usuario,
            'estado' => false,
        ]);

        Mail::assertSent(BajaUsuario::class, function ($mail) use ($empleado) {
            return $mail->hasTo($empleado->usuario->correo);
        });
    }

    public function test_dar_de_alta_empleado()
    {
        $token = $this->loginComoPropietario();
        $empleado = Empleado::first();

        // Dar de baja primero
        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/empleado/dar-baja/{$empleado->id_usuario}");

        // Ahora, dar de alta
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/empleado/dar-alta/{$empleado->id_usuario}");

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Empleado activado']);

        $this->assertDatabaseHas('usuarios', [
            'id' => $empleado->id_usuario,
            'estado' => true,
        ]);

        Mail::assertSent(AltaUsuario::class, function ($mail) use ($empleado) {
            return $mail->hasTo($empleado->usuario->correo);
        });
    }
}
