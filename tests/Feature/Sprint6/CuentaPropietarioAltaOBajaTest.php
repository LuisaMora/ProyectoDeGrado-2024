<?php

namespace Tests\Feature\Sprint6;

use App\Mail\AltaUsuario;
use App\Mail\BajaUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\EstadoPedido;
use App\Models\FormularioPreRegistro;
use App\Models\Propietario;
use App\Models\Restaurante;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CuentaPropietarioAltaOBajaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();         // Finge los eventos para que no se emitan realmente
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
    }

    private function loginComoAdmin(): string
    {
        // Realiza el login del administrador y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'administrador',
            'password' => '12345678',
        ]);

        return $response['token'];
    }

    public function test_dar_baja_a_propietario()
    {
        // Obtener un propietario creado
        // Asegúrate de que hay un propietario en
        // la base de datos
        $propietario = Propietario::first(); 
        

        // Asumimos que el propietario está activo
        $this->assertTrue($propietario->usuario->estado == '1');

        // Simular el inicio de sesión del administrador
        $token = $this->loginComoAdmin(); // Asegúrate de tener este método

        // Hacer la petición para dar de baja
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/propietario/dar-baja/' . $propietario->id_usuario);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);
        $this->assertStringContainsString('dado de baja', $response['message']);

        // Verificar que el estado del propietario se ha actualizado
        $this->assertDatabaseHas('usuarios', [
            'id' => $propietario->id_usuario,
            'estado' => false, // Este debe ser el valor que representa "dado de baja"
        ]);

        // Aquí verificar si se envió el correo de baja
        Mail::assertSent(BajaUsuario::class, function ($mail) use ($propietario) {
            return $mail->usuario->id === $propietario->id_usuario; // Verifica el 
            //destinatario del correo
        });
    }


    public function test_dar_alta_a_propietario()
    {
        // Obtener un propietario creado y darlo de baja primero
        $propietario = Propietario::first(); // Asegúrate de que hay un propietario en la base de datos
        $propietario->usuario->estado = false;
        $propietario->usuario->save();

        // Simular el inicio de sesión del administrador
        $token = $this->loginComoAdmin(); // Asegúrate de tener este método

        // Hacer la petición para dar de alta
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/propietario/dar-alta/' . $propietario->id_usuario);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);
        $this->assertStringContainsString('activado', $response['message']);

        // Verificar que el estado del propietario se ha actualizado
        $this->assertDatabaseHas('usuarios', [
            'id' => $propietario->id_usuario,
            'estado' => true,
        ]);

        // Verificar que el correo de alta fue enviado
        Mail::assertSent(AltaUsuario::class, function ($mail) use ($propietario) {
            return $mail->usuario->id === $propietario->id_usuario; // Verifica el destinatario del correo
        });
    }

    public function test_dar_baja_usuario_no_encontrado()
    {
        // Simular el inicio de sesión del administrador
        $token = $this->loginComoAdmin(); // Asegúrate de tener este método

        // Hacer la petición para dar de baja a un usuario que no existe
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/propietario/dar-baja/99999'); // ID que no existe

        // Verificar que la respuesta sea un error
        $response->assertStatus(404);
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('Usuario no encontrado', $response['message']);
    }

    public function test_dar_baja_propietario_no_encontrado()
    {
        // Simular el inicio de sesión del administrador
        $token = $this->loginComoAdmin(); // Asegúrate de tener este método

        // Hacer la petición para dar de baja a un propietario que no existe
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/propietario/dar-baja/99999'); // ID que no existe

        // Verificar que la respuesta sea un error
        $response->assertStatus(404);
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('Usuario no encontrado', $response['message']);
    }

    public function test_obtener_lista_propietarios()
    {
        // con este serán dos propietarios creados
        $propietario =  Propietario::factory()->asignarDatosSesion('propietarioA2', 'propietario2@gmail.com')->create();

        $token = $this->loginComoAdmin(); // Asegúrate de tener este método

        // Hacer la petición para obtener la lista de propietarios
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/propietarios');

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);

        // Verificar que los datos de los propietarios estén en la respuesta
        $this->assertNotEmpty($response['data']);
        $this->assertCount(2, $response['data']); // Debería haber 2 propietario en este caso
        $this->assertEquals($propietario->id_usuario, $response['data'][1]['id_usuario']); // Verifica que el ID coincida
    }

}
