<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\ActivityLogService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ventaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-venta|crear-venta|mostrar-venta|eliminar-venta', ['only' => ['index']]);
        $this->middleware('permission:crear-venta', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-venta', ['only' => ['show']]);
        $this->middleware('permission:eliminar-venta', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $ventas = Venta::with(['comprobante', 'cliente.persona', 'user'])
            ->where('estado', 1)
            ->latest()
            ->get();

        return view('venta.index', compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $subquery = DB::table('compra_producto')
            ->select('producto_id', DB::raw('MAX(created_at) as max_created_at'))
            ->groupBy('producto_id');

        $productos = Producto::join('compra_producto as cpr', function ($join) use ($subquery) {
            $join->on('cpr.producto_id', '=', 'productos.id')
                ->whereIn('cpr.created_at', function ($query) use ($subquery) {
                    $query->select('max_created_at')
                        ->fromSub($subquery, 'subquery')
                        ->whereRaw('subquery.producto_id = cpr.producto_id');
                });
        })
            ->select('productos.nombre', 'productos.id', 'productos.stock', 'cpr.precio_venta')
            ->where('productos.estado', 1)
            ->where('productos.stock', '>', 0)
            ->get();

        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();
        $comprobantes = Comprobante::all();

        return view('venta.create', compact('productos', 'clientes', 'comprobantes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Llenar mi tabla venta
            $venta = Venta::create($request->validated());

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->validated())->only(['cliente_id', 'comprobante_id', 'total'])->toArray();

            // Registrar la actividad
            ActivityLogService::log('Venta creada', 'ventas', $logData);

            // Llenar mi tabla venta_producto
            $arrayProducto_id = $request->get('arrayidproducto');
            $arrayCantidad = $request->get('arraycantidad');
            $arrayPrecioVenta = $request->get('arrayprecioventa');
            $arrayDescuento = $request->get('arraydescuento');

            $sizeArray = count($arrayProducto_id);
            $cont = 0;

            while ($cont < $sizeArray) {
                $venta->productos()->syncWithoutDetaching([
                    $arrayProducto_id[$cont] => [
                        'cantidad' => $arrayCantidad[$cont],
                        'precio_venta' => $arrayPrecioVenta[$cont],
                        'descuento' => $arrayDescuento[$cont]
                    ]
                ]);

                // Actualizar stock
                $producto = Producto::find($arrayProducto_id[$cont]);
                $stockActual = $producto->stock;
                $cantidad = intval($arrayCantidad[$cont]);

                DB::table('productos')
                    ->where('id', $producto->id)
                    ->update([
                        'stock' => $stockActual - $cantidad
                    ]);

                $cont++;
            }

            DB::commit();

            return redirect()->route('ventas.index')->with('success', 'Venta registrada');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear la venta:', ['error' => $e->getMessage()]);
            return redirect()->route('ventas.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta): View
    {
        // Registrar la actividad
        ActivityLogService::log('Venta visualizada', 'ventas', [
            'cliente_id' => $venta->cliente_id,
            'comprobante_id' => $venta->comprobante_id,
            'total' => $venta->total,
        ]);

        return view('venta.show', compact('venta'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $venta = Venta::findOrFail($id);

            // Eliminar lógicamente la venta
            $venta->update(['estado' => 0]);

            // Registrar la actividad
            ActivityLogService::log('Venta eliminada', 'ventas', [
                'cliente_id' => $venta->cliente_id,
                'comprobante_id' => $venta->comprobante_id,
                'total' => $venta->total,
            ]);

            DB::commit();

            return redirect()->route('ventas.index')->with('success', 'Venta eliminada');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar la venta:', ['error' => $e->getMessage()]);
            return redirect()->route('ventas.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }
}
