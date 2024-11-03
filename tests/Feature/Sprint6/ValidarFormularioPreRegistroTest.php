<?php

namespace Tests\Feature\Sprint6;

use App\Mail\ConfirmacionPreRegistro;
use App\Mail\RechazoPreRegistro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\EstadoPedido;
use App\Models\FormularioPreRegistro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ValidarFormularioPreRegistroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatosIniciales();
        Event::fake(); // Finge los eventos
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
        ];

        foreach ($estados as $estado) {
            EstadoPedido::create($estado);
        }

        // Creación de usuario administrador
        Administrador::factory()->asignarDatosSesion('administrador', 'admin@gmail.com')->create();

        // Crear datos de prueba para los formularios de pre-registro
        FormularioPreRegistro::factory()->create([
            'nombre_restaurante' => 'Restaurante Uno',
            'updated_at' => now()->subDays(1),
        ]);

        FormularioPreRegistro::factory()->create([
            'nombre_restaurante' => 'Restaurante Dos',
            'updated_at' => now(),
        ]);
        // Crear formularios de pre-registro con datos similares id 3 y4
        FormularioPreRegistro::factory()->create([
            'nombre_restaurante' => 'Restaurante Prueba',
            'nit' => '12345678',
            'correo_propietario' => 'propietario1@correo.com',
            'correo_restaurante' => 'restP@gmail.com',
            'estado' => 'pendiente',
        ]);

        FormularioPreRegistro::factory()->create([
            'nombre_restaurante' => 'Restaurante Duplicado',
            'nit' => '12345678',
            'correo_propietario' => 'propietario1@correo.com',
            'estado' => 'pendiente'
        ]);

        FormularioPreRegistro::factory()->create([
            'nombre_restaurante' => 'Restaurante Prueba',
            'nit' => '12345678910',
            'correo_propietario' => 'propietariito2@correo.com',
            'correo_restaurante' => 'restaurante@gmail.com',
            'estado' => 'pendiente'
        ]);
    }

    private function loginComoAdmin(): string
    {
        // Realiza el login del propietario y devuelve el token
        $response = $this->postJson('/api/login', [
            'usuario' => 'administrador',
            'password' => '12345678',
        ]);

        return $response['token'];
    }

    public function test_confirmar_pre_registro_cambia_estado_y_envia_correos_correctamente()
    {
        $token = $this->loginComoAdmin();

        // Simular la confirmación del formulario
        // Realizamos la solicitud para confirmar el pre-registro con el estado 'aceptado'
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/pre-registro/confirmar?pre_registro_id=3&estado=aceptado');

        // Comprobar que la respuesta es exitosa (200 OK) y obtener más detalles en caso de error
        $response->assertStatus(200);

        // Verificar el estado del formulario en la base de datos
        // Confirmamos que el formulario con ID 3 ahora tiene el estado 'aceptado'
        $this->assertDatabaseHas('formulario_pre_registro', [
            'id' => 3,
            'estado' => 'aceptado'
        ]);

        // Confirmamos que otros formularios del mismo propietario han sido marcados como 'rechazado'
        $this->assertDatabaseHas('formulario_pre_registro', [
            'id' => 4,
            'estado' => 'rechazado'
        ]);

        // Verificación del envío de correos
        // Verificamos que el correo de confirmación fue enviado al propietario del restaurante
        Mail::assertSent(ConfirmacionPreRegistro::class, function ($mail) {
            return $mail->hasTo('propietario1@correo.com');
        });

        // Verificamos que el correo de confirmación fue enviado también al correo del restaurante
        Mail::assertSent(ConfirmacionPreRegistro::class, function ($mail) {
            return $mail->hasTo('restP@gmail.com');
        });
    }

    public function test_rechaza_formularios_duplicados_y_actualiza_estado()
    {
        // Crear un formulario pendiente de pre-registro
        $formularioPendiente = FormularioPreRegistro::factory()->create([
            'nombre_restaurante' => 'Restaurante Prueba',
            'nit' => '1234567890',
            'correo_propietario' => 'correo@duplicado.com',
            'cedula_identidad_propietario' => '12345678',
            'estado' => 'pendiente',
            'celular_restaurante' => '123456789'
        ]);

        // Crear otro formulario pendiente con el mismo NIT y correo para provocar duplicación
        $formularioDuplicado = FormularioPreRegistro::factory()->create([
            'nombre_restaurante' => 'Otro Restaurante',
            'nit' => '1234567890', // mismo NIT
            'correo_propietario' => 'correo@duplicado.com', // mismo correo
            'cedula_identidad_propietario' => '87654321',
            'estado' => 'pendiente',
            'celular_restaurante' => '987654321'
        ]);

        // Realizar la acción de confirmación en el formulario duplicado
        $token = $this->loginComoAdmin();
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/pre-registro/confirmar?pre_registro_id=' . $formularioDuplicado->id . '&estado=aceptado&motivo_rechazo=Datos incompletos');

        // Verificar que el estado del primer formulario fue cambiado a 'rechazado'
        $this->assertDatabaseHas('formulario_pre_registro', [
            'id' => $formularioPendiente->id,
            'estado' => 'rechazado',
        ]);

        // Verificar que el segundo formulario se aceptó
        $this->assertDatabaseHas('formulario_pre_registro', [
            'id' => $formularioDuplicado->id,
            'estado' => 'aceptado',
        ]);

        // Verificar que la respuesta es correcta
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);
    }

    public function test_rechazar_pre_registro_cambia_estado_y_envia_correos_correctamente()
    {
        // Configuración inicial y autenticación

        $token = $this->loginComoAdmin();

        // Realizamos la solicitud para rechazar el pre-registro con el estado 'rechazado'
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/pre-registro/confirmar?pre_registro_id=5&estado=rechazado&motivo_rechazo=Datos incompletos');

        // Comprobar que la respuesta es exitosa (200 OK) y obtener más detalles en caso de error
        $response->assertStatus(200);

        // Verificar el estado del formulario en la base de datos
        // Confirmamos que el formulario con ID 3 ahora tiene el estado 'rechazado'
        $this->assertDatabaseHas('formulario_pre_registro', [
            'id' => 5,
            'estado' => 'rechazado'
        ]);

        // Verificación del envío de correos de rechazo
        // Verificamos que el correo de rechazo fue enviado al propietario del restaurante
        Mail::assertSent(RechazoPreRegistro::class, function ($mail) {
            return $mail->hasTo('propietariito2@correo.com');
        });

        // Verificamos que el correo de rechazo fue enviado también al correo del restaurante
        Mail::assertSent(RechazoPreRegistro::class, function ($mail) {
            return $mail->hasTo('restaurante@gmail.com');
        });
    }
}
