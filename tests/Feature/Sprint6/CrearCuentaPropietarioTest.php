<?php

namespace Tests\Feature\Sprint6;

use App\Mail\ConfirmacionPreRegistro;
use App\Mail\RechazoPreRegistro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\EstadoPedido;
use App\Models\FormularioPreRegistro;
use App\Models\Restaurante;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CrearCuentaPropietarioTest extends TestCase
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
            'cedula_identidad_propietario' => '8739917'
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

    public function test_crea_cuenta_de_propietario_restaurante_y_menu_automaticamente()
    {


        // Ejecutar la confirmación del formulario
        $token = $this->loginComoAdmin();
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/pre-registro/confirmar?pre_registro_id=' . 3 . '&estado=aceptado');

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(200);

        // Verificar que se haya creado el restaurante correspondiente
        $this->assertDatabaseHas('restaurantes', [
            'nombre' => 'RestaurantePrueba',
            'nit' => '12345678',
        ]);

        // Verificar que se haya creado un propietario con el correo del formulario
        $this->assertDatabaseHas('propietarios', [
            'ci' => '8739917',
        ]);

        // Verificar que se haya creado el menú asociado al restaurante
        $restaurante = Restaurante::where('id', '1')->first();
        $this->assertNotNull($restaurante, 'El restaurante no fue creado.');
        $this->assertDatabaseHas('menus', [
            'id' => 1,
        ]);

        // Confirmar la actualización de estado en el formulario original
        $this->assertDatabaseHas('formulario_pre_registro', [
            'id' => 3,
            'estado' => 'aceptado',
        ]);
    }
}
