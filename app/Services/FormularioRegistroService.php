<?php

namespace App\Services;

use App\Mail\ConfirmacionPreRegistro;
use App\Mail\RechazoPreRegistro;
use App\Repositories\CategoriaRepository;
use App\Repositories\MenuRepository;
use App\Repositories\MesaRepository;
use App\Repositories\PreRegistroRepository;
use App\Repositories\PropietarioRepository;
use App\Repositories\RestauranteRepository;
use App\Repositories\UsuarioRepository;
use App\Utils\ImageHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FormularioRegistroService
{

    public function __construct(private PreRegistroRepository $preRegistroRepository,
    private EmailService $emailService, private UsuarioRepository $usuarioRepository,
    private RestauranteRepository $restauranteRepository, private MesaRepository $mesaRepository,
    private PropietarioRepository $propietarioRepository,private MenuRepository $menuRepository, private CategoriaRepository $categoriaRepository)
    {
    }
    
    public function getFormularios()
    {
        return $this->preRegistroRepository->all();
    }

    public function getFormulario(int $id)
    {
        return $this->preRegistroRepository->find($id);
    }
    
    public function recibirFormulario(array $formulario)
    {
        $imagen = $formulario['fotografia_propietario'];
        $nombreCarpeta = 'fotografias_propietarios';
        $urlImagen = ImageHandler::guardarArchivo($imagen, $nombreCarpeta);

        $imagen = $formulario['licencia_funcionamiento'];
        $nombreCarpeta = 'licencias_funcionamiento';
        $urlPdf = ImageHandler::guardarArchivo($imagen, $nombreCarpeta);

        $formulario['fotografia_propietario'] = $urlImagen;
        $formulario['licencia_funcionamiento'] = $urlPdf;

        $this->preRegistroRepository->create($formulario);

    }

    public function validarFormulario(int $preRegistroId, string $estado): Model
    {
        try
        {
            DB::beginTransaction();
            $formPreRegistro = $this->preRegistroRepository->find($preRegistroId);
            if (!$formPreRegistro || $formPreRegistro->estado != 'pendiente')
            {
                throw new \Exception('El formulario ya fue confirmado o no existe', 400);
            }
            $formPreRegistro = $this->preRegistroRepository->update($preRegistroId, ['estado' => $estado]);
            $this->preRegistroRepository->rechazarFormularios($formPreRegistro->nit, $formPreRegistro->correo_propietario, $formPreRegistro->cedula_identidad_propietario);

            if ($estado === 'aceptado')
            {
                $this->aceptarFormulario($formPreRegistro);
            }
            else
            {
                $this->rechazarFormulario($formPreRegistro);
            }
            return $formPreRegistro;
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw $e;
        }
    }

    public function aceptarFormulario($formulario)
    {
        $formulario->password = bcrypt('12345678');
        $formulario->apellido_materno = $formulario->apellido_materno_propietario;
        $formulario->nombre = $formulario->nombre_propietario;
        $formulario->apellido_paterno = $formulario->apellido_paterno_propietario;
        $formulario->nickname = str_replace(' ', '', $formulario->nombre_restaurante) . $formulario->nit;
        $formulario->tipo_usuario = 'Propietario';
        $formulario->foto_perfil = $formulario->fotografia_propietario;
        $formulario->correo = $formulario->correo_propietario;
        $usuario = $this->usuarioRepository->create($formulario->toArray());
        $menu = $this->menuRepository->create();
        // print_r($menu);
        $categoria = $this->categoriaRepository->create(['nombre' => 'Otros', 'imagen' => 'default_dir', 'id_menu' => $menu->id]);
        // print_r($categoria);
        $restaurante = [
            'nombre' => str_replace(' ', '', $formulario['nombre_restaurante']),
            'nit' => $formulario['nit'],
            'celular' => $formulario['celular_restaurante'],
            'correo' => $formulario['correo_restaurante'],
            'latitud' => $formulario['latitud'],
            'longitud' => $formulario['longitud'],
            'licencia_funcionamiento' => $formulario['licencia_funcionamiento'],
            'tipo_establecimiento' => $formulario['tipo_establecimiento'],
            'id_menu' => $menu->id
        ];
        $restaurante = $this->restauranteRepository->create($restaurante);

        $propietario = [
            'id_usuario' => $usuario->id,
            'id_restaurante' => $restaurante->id,
            'id_administrador' => auth()->user()->id,
            'ci' => $formulario['cedula_identidad_propietario'],
            'fecha_registro' => now(),
            'pais' => $formulario['pais'],
            'departamento' => $formulario['departamento']
        ];

        $this->propietarioRepository->create($propietario);
        $this->crearMesas($formulario['numero_mesas'], $restaurante->id);

        $this->emailService->sendEmail($formulario['correo_propietario'], new ConfirmacionPreRegistro($usuario, $restaurante));
        
        $this->emailService->sendEmail($formulario['correo_restaurante'], new ConfirmacionPreRegistro($usuario, $restaurante));
        DB::commit();

        return true;
    }

    public function rechazarFormulario($formulario)
    {
        $mensaje  = $formulario['motivo_rechazo'];
        $this->emailService->sendEmail($formulario['correo_propietario'], new RechazoPreRegistro($formulario, $mensaje));
        
        $this->emailService->sendEmail($formulario['correo_restaurante'], new RechazoPreRegistro($formulario, $mensaje));
        DB::commit();
        return true;
    }

    private function crearMesas(int $numeroMesas, int $idRestaurante){
        $nroMesa = 1;
        for ($i = 0; $i < $numeroMesas; $i++) {
            $this->mesaRepository->crearMesa([
                'nombre' => 'Mesa '.$nroMesa,
                'id_restaurante' => $idRestaurante
            ]);
            $nroMesa++;
        }
    }
}