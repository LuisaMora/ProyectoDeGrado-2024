<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CuentaController extends Controller
{
    public function index($idRestaurante){
        $cuentas = Cuenta::with(['mesa', 'pedidos' => function ($query) {
            $query->whereDate('fecha_hora_pedido', now())
                ->with(['platos' => function ($query) {
                    // Aquí seleccionamos los campos que necesitamos de la tabla pivot 'plato_pedido', 
                    // incluyendo 'precio_fijado' en lugar del precio actual de la tabla 'platillos'
                    $query->select('platillos.id', 'platillos.nombre', 'plato_pedido.precio_fijado', 'plato_pedido.cantidad');
                }]);
        }])->whereHas('mesa', function ($query) use ($idRestaurante) {
            $query->where('id_restaurante', $idRestaurante);
        })->where('estado', '!=', 'Pagada')
        ->get();

        if ($cuentas->isEmpty()) {
            return response()->json(['status' => 'error', 'error' => 'No hay cuentas disponibles.'], 404);
        }
        // return response()->json(['status' => 'success', 'cuentas' => $cuentas], 200);

        $cuentasProcesadas = $this->procesarDatos($cuentas->toArray());
        return response()->json(['status' => 'success', 'cuentas' => $cuentasProcesadas], 200);
    }

    public function store(Request $request, $idCuenta)
    {
        // Validar los datos
        $validarDatos = Validator::make($request->all(), [
            'razon_social' => 'nullable|string|max:255',
            'nit' => 'nullable|string|max:20',
        ]);

        if ($validarDatos->fails()) {
            return response()->json(['status' => 'error', 'error' => $validarDatos->errors()], 400);
        }


        $cuenta = Cuenta::find($idCuenta);
        if (!$cuenta) {
            return response()->json(['status' => 'error', 'error' => 'No se encontró una cuenta con el ID proporcionado.'], 404);
        }
        $cuenta->nombre_razon_social = $request->razon_social;
        $cuenta->nit = $request->nit;
        $cuenta->save();

        return response()->json(['status' => 'success', 'cuenta' => $cuenta], 200);
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
