<?php
namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CuentaController extends Controller
{

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
        $cuenta = Cuenta::with(['mesa','pedidos' => function ($query) {
            $query->whereDate('fecha_hora_pedido', now())
                  ->with(['platos' => function ($query) {
                      $query->select('platillos.id', 'platillos.nombre', 'platillos.precio'); // Campos específicos de platillos
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
                    'precio' => $plato['precio'],
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
        $cuenta = Cuenta::find($idCuenta);
        if (!$cuenta) {
            return response()->json(['status' => 'error', 'error' => 'Cuenta no encontrada.'], 404);
        }
        $cuenta->estado = 'Pagada';
        $cuenta->save();
    
        return response()->json(['status' => 'success', 'message' => 'Cuenta cerrada con éxito.', 'cuenta' => $cuenta], 200);
    }

}