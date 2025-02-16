<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCuentaConsumo;
use App\Http\Requests\StoreCuentaRequest;
use App\Models\Cuenta;
use App\Models\Pedido;
use App\Services\CuentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CuentaController extends Controller
{
    public function __construct(private CuentaService $cuentaService)
    {
    }

    public function index($id_restaurante)
    {
        try {
            $cuentas = $this->cuentaService->getCuentasByRestaurante($id_restaurante);
            return response()->json(['status' => 'success', 'cuentas' => $cuentas], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreCuentaRequest $request, $idCuenta)
    {
        try {
            $cuenta = $this->cuentaService->updateCuenta($idCuenta, $request->all());
            return response()->json(['status' => 'success', 'cuenta' => $cuenta], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());

        }
    }

    public function show($idCuenta)
    {
        $cuenta = Cuenta::with(['mesa', 'pedidos' => function ($query) {
            $query->whereDate('fecha_hora_pedido', now())
                ->with(['platos' => function ($query) {
                    // Aquí seleccionamos los campos que necesitamos de la tabla pivot 'plato_pedido', 
                    // incluyendo 'precio_fijado' en lugar del precio actual de la tabla 'platillos'
                    $query->select('platillos.id', 'platillos.nombre', 'plato_pedido.precio_fijado', 'plato_pedido.cantidad');
                }]);
        }])->find($idCuenta);

        if (!$cuenta) {
            return response()->json(['status' => 'error', 'error' => 'Cuenta no encontrada.'], 404);
        }

        $cuentaProcesada = $this->procesarDatos([$cuenta]);
        return response()->json(['status' => 'success', 'cuenta' => $cuentaProcesada[0]], 200);
    }


    private function procesarDatos($cuentas)
    {
        $resultados = [];

        foreach ($cuentas as $cuenta) {
            // Inicializamos una nueva cuenta
            $nuevaCuenta = [
                'id' => $cuenta['id'],
                'id_mesa' => $cuenta['id_mesa'],
                'nombre_mesa' => $cuenta['mesa']['nombre'],
                'estado' => $cuenta['estado'],
                'nombre_razon_social' => $cuenta['nombre_razon_social'],
                'monto_total' => $cuenta['monto_total'],
                'nit' => $cuenta['nit'],
                'platos' => [] // Aquí guardaremos los platos de todos los pedidos
            ];

            // Iteramos sobre cada pedido de la cuenta
            foreach ($cuenta['pedidos'] as $pedido) {
                // Iteramos sobre los platos de cada pedido y los agregamos a la cuenta
                foreach ($pedido['platos'] as $plato) {
                    $nuevaCuenta['platos'][] = [
                        'id' => $plato['id'],
                        'nombre' => $plato['nombre'],
                        'precio' => $plato['precio_fijado'], // Usamos el precio guardado en 'plato_pedido'
                        'id_pedido' => $plato['pivot']['id_pedido'],
                        'id_platillo' => $plato['pivot']['id_platillo'],
                        'cantidad' => $plato['pivot']['cantidad']
                    ];
                }
            }

            // Agregamos la nueva cuenta transformada al resultado final
            $resultados[] = $nuevaCuenta;
        }

        return $resultados;
    }



    public function close($idCuenta)
    {
        $estadoServido = 4;
        $pedidosNoServidos = Pedido::where('id_estado','!=', $estadoServido)
                                    ->where('id_cuenta',$idCuenta)->get();
        if ($pedidosNoServidos->count() > 0) {
            return response()->json(['status' => 'error', 'error' => 'Hay pedidos sin servir.'], 400);
        }
        $cuenta = Cuenta::find($idCuenta);
        if (!$cuenta) {
            return response()->json(['status' => 'error', 'error' => 'Cuenta no encontrada.'], 404);
        }
        $cuenta->estado = 'Pagada';
        $cuenta->save();

        return response()->json(['status' => 'success', 'message' => 'Cuenta cerrada con éxito.', 'cuenta' => $cuenta], 200);
    }

    public function showCerradas($idRestaurante)
    {
        // Query to get all accounts with estado 'Pagada'
        // $idRestaurante = '2';
        $cuentasCerradas = Cuenta::with(['mesa', 'pedidos' => function ($query) {
            $query->whereDate('fecha_hora_pedido', now())
                ->with(['platos' => function ($query) {
                    // Aquí seleccionamos los campos que necesitamos de la tabla pivot 'plato_pedido', 
                    // incluyendo 'precio_fijado' en lugar del precio actual de la tabla 'platillos'
                    $query->select('platillos.id', 'platillos.nombre', 'plato_pedido.precio_fijado', 'plato_pedido.cantidad');
                }]);
        }])->whereHas('mesa', function ($query) use ($idRestaurante) {
            $query->where('id_restaurante', $idRestaurante);
        })->where('estado', 'Pagada')
        ->get();

        // Check if there are closed accounts
        if ($cuentasCerradas->isEmpty()) {
            return response()->json(['status' => 'error', 'error' => 'No hay cuentas cerradas.'], 404);
        }

        // Process the data with the procesarDatos method
        $cuentasProcesadas = $this->procesarDatos($cuentasCerradas->toArray());

        return response()->json(['status' => 'success', 'cuentas' => $cuentasProcesadas], 200);
    }
}
