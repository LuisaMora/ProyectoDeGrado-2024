<?php

namespace Tests\Feature\Sprint6;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Administrador;
use App\Models\EstadoPedido;
use App\Models\FormularioPreRegistro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VisualizarFormularioPreRegistroTest extends TestCase
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

    public function test_visualiza_todos_los_formularios_para_el_pre_registro_de_restaurantes()
    {
        $token = $this->loginComoAdmin();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/pre-registros');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        // Verifica que los formularios están en el orden correcto (por fecha de actualización descendente)
        $data = $response->json('data');
        $this->assertEquals('Restaurante Dos', $data[0]['nombre_restaurante']);
        $this->assertEquals('Restaurante Uno', $data[1]['nombre_restaurante']);
    }
}
