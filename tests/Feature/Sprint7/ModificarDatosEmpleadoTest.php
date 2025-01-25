<?php

namespace Tests\Feature\Sprint7;

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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModificarDatosEmpleadoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatosIniciales();
        Storage::fake('public'); 
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

        //  Insertar platillos al restaurante1

        // Creación de empleado mesero asociado al propietario
        Empleado::create([
            'id_usuario' => User::factory()
                ->asignarNicknameCorreo('empleado1', 'empleado1@gmail.com')->create([
                    'tipo_usuario' => 'Empleado'
                ])->id,
            'id_rol' => 1,
            'id_propietario' => 1,
            'fecha_nacimiento' => '1999-01-01',
            'fecha_contratacion' => now(),
            'ci' => '70951529',
            'direccion' => 'Cochabamba',
            'id_restaurante' =>1
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
            'id_restaurante' =>1
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
            'id_restaurante' =>1
        ]);

    }

    public function test_modificar_datos_mesero() {
        // Loguear como mesero para obtener el token
        $usuarioMesero = $this->postJson('/api/login', [
            'usuario' => 'empleado1', // Cambia a 'empleado1' para obtener el token del mesero
            'password' => '12345678',
        ]);
    
        $tokenMesero = $usuarioMesero['token'];
    
        // Llamar a la ruta para actualizar los datos del empleado
        $response = $this->withHeader('Authorization', "Bearer $tokenMesero")
            ->postJson('/api/actualizar/datos-empleado', [
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'Gómez',
                'nickname' => 'juanito',
            ]);
    
        // verificar respuesta
        $response->assertStatus(200);
        $this->assertEquals('Datos actualizados correctamente', $response->json('message'));
    
        // Aserciones para verificar que los datos se han actualizado en la base de datos
        $this->assertDatabaseHas('usuarios', [
            'id' => $usuarioMesero['user']['usuario']['id'], // Asegúrate de usar el ID del usuario correcto
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Gómez',
            'nickname' => 'juanito',
        ]);
    

    }

    public function test_modificar_datos_cocinero() {
        // Loguear como cocinero para obtener el token
        $usuarioCocinero = $this->postJson('/api/login', [
            'usuario' => 'cocinero1',
            'password' => '12345678',
        ]);
    
        $tokenCocinero = $usuarioCocinero['token'];
    
        // Llamar a la ruta para actualizar los datos del empleado
        $response = $this->withHeader('Authorization', "Bearer $tokenCocinero")
            ->postJson('/api/actualizar/datos-empleado', [
                'nombre' => 'Carlos',
                'apellido_paterno' => 'Martínez',
                'apellido_materno' => 'López',
                'nickname' => 'carlitos',
            ]);
    
        // Verificar respuesta
        $response->assertStatus(200);
        $this->assertEquals('Datos actualizados correctamente', $response->json('message'));
    
        // Aserciones para verificar que los datos se han actualizado en la base de datos
        $this->assertDatabaseHas('usuarios', [
            'id' => $usuarioCocinero['user']['usuario']['id'], // Asegúrate de usar el ID del usuario correcto
            'nombre' => 'Carlos',
            'apellido_paterno' => 'Martínez',
            'apellido_materno' => 'López',
            'nickname' => 'carlitos',
        ]);
    }

    public function test_modificar_datos_cajero() {
        // Loguear como cajero para obtener el token
        $usuarioCajero = $this->postJson('/api/login', [
            'usuario' => 'cajero1',
            'password' => '12345678',
        ]);
    
        $tokenCajero = $usuarioCajero['token'];
    
        // Llamar a la ruta para actualizar los datos del empleado
        $response = $this->withHeader('Authorization', "Bearer $tokenCajero")
            ->postJson('/api/actualizar/datos-empleado', [
                'nombre' => 'Ana',
                'apellido_paterno' => 'González',
                'apellido_materno' => 'Rodríguez',
                'nickname' => 'anita',
            ]);
    
        // Verificar respuesta
        $response->assertStatus(200);
        $this->assertEquals('Datos actualizados correctamente', $response->json('message'));
    
        // Aserciones para verificar que los datos se han actualizado en la base de datos
        $this->assertDatabaseHas('usuarios', [
            'id' => $usuarioCajero['user']['usuario']['id'], // Asegúrate de usar el ID del usuario correcto
            'nombre' => 'Ana',
            'apellido_paterno' => 'González',
            'apellido_materno' => 'Rodríguez',
            'nickname' => 'anita',
        ]);
    }
    
    
}    
