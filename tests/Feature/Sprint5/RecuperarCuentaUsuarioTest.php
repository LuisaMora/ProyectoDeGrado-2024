<?php

namespace Tests\Feature\Sprint5;

use App\Mail\ResetPasswordMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Empleado;
use App\Models\EstadoPedido;
use App\Models\Mesa;
use App\Models\Platillo;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RecuperarCuentaUsuarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();         // Finge los eventos para que no se emitan realmente   
        Mail::fake(); // Fakeamos el envío de correo
        $this->setUpDatosIniciales();
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

        // Inserción de categorías
        $categorias = ['Otros', 'Desayunos', 'Comida', 'Cena', 'Bebidas', 'Postres'];
        foreach ($categorias as $categoria) {
            Categoria::factory()->create(['nombre' => $categoria]);
        }

        // Inserción de mesas
        Mesa::factory(2)->registrar_a_restaurante(1)->create();

        // Creación de empleado asociado al propietario
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('empleado1', 'empleado1@gmail.com')->create([
                    'tipo_usuario' => 'Empleado'
                ])->id,
            'id_rol' => 1,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
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
            'id_rol' => 3,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951561',
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
            'fecha_nacimiento' => '1990-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951561',
            'direccion' => 'Cochabamba',
            'id_restaurante' => 1
        ]);
    }

    public function test_solicitar_cambio_contrasenia_genera_token_y_envia_correo()
    {
        // Dirección de frontend para el enlace de restablecimiento
        $direccionFrontend = 'https://frontend-app.com/reset-password';

        // Realizamos la solicitud de cambio de contraseña
        $response = $this->postJson('/api/solicitar-cambio-contrasenia', [
            'correo' => 'propietario@gmail.com',
            'direccion_frontend' => $direccionFrontend,
        ]);

        // Verificamos que la respuesta es exitosa
        $response->assertStatus(200)
            ->assertJson(['message' => 'Correo de restablecimiento enviado.']);

        // Recargamos el usuario desde la base de datos para verificar el token
        $user = User::find(2);

        // Verificar que el token y la fecha de expiración se hayan generado
        $this->assertNotNull($user->reset_token);
        $this->assertNotNull($user->reset_token_expires_at);
        // $this->assertTrue($user->reset_token_expires_at->greaterThan(now()));

        // Verificar que se haya enviado el correo
        Mail::assertSent(ResetPasswordMail::class, function ($mail) use ($user, $direccionFrontend) {
            return $mail->hasTo($user->correo) &&
                $mail->token === $user->reset_token &&
                $mail->direccion_front === $direccionFrontend;
        });
    }

    public function test_solicitar_cambio_contrasenia_correo_invalido()
    {
        // Dirección de frontend para el enlace de restablecimiento
        $direccionFrontend = 'https://frontend-app.com/reset-password';

        // Realizamos la solicitud de cambio de contraseña
        $response = $this->postJson('/api/solicitar-cambio-contrasenia', [
            'correo' => 'propietarie@gmail.com',
            'direccion_frontend' => $direccionFrontend,
        ]);

        // Verificamos que la respuesta es exitosa
        $response->assertStatus(422);
    }

    public function test_restablecer_contrasenia_con_token_valido()
    {
        // Dirección de frontend para restablecimiento
        $direccionFrontend = 'https://frontend-app.com/reset-password';

        // Solicitar cambio de contraseña para generar un token
        $this->postJson('/api/solicitar-cambio-contrasenia', [
            'correo' => 'propietario@gmail.com',
            'direccion_frontend' => $direccionFrontend,
        ]);

        // Recargar el usuario para obtener el token generado
        $user = User::where('correo', 'propietario@gmail.com')->first();
        $resetToken = $user->reset_token;

        // Realizar la solicitud para restablecer la contraseña
        $response = $this->postJson('/api/restablecer-contrasenia-olvidada', [
            'token' => $resetToken,
            'newPassword' => 'nuevaContrasenia123',
        ]);

        // Verificar respuesta exitosa
        $response->assertStatus(200)
            ->assertJson(['message' => 'Contraseña actualizada correctamente.']);

        // Recargar el usuario y verificar que el token se haya eliminado y la contraseña se haya actualizado
        $user->refresh();
        $this->assertNull($user->reset_token);
        $this->assertNull($user->reset_token_expires_at);
        $this->assertTrue(Hash::check('nuevaContrasenia123', $user->password));
    }

    public function test_restablecer_contrasenia_con_token_invalido()
    {
        // Intentar restablecer contraseña con un token inválido
        $response = $this->postJson('/api/restablecer-contrasenia-olvidada', [
            'token' => 'tokenInvalido123tokenInvalido123tokenInvalido123tokenInvalid',
            'newPassword' => 'nuevaContrasenia123',
        ]);

        $response->dump();

        // Verificar respuesta de error
        $response->assertStatus(400)
            ->assertJson(['message' => 'Token inválido o expirado.']);
    }
}
