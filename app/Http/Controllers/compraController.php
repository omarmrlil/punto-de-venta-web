<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Proveedore;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogService;
use Illuminate\Routing\Controller;


class compraController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-compra|crear-compra|mostrar-compra|eliminar-compra', ['only' => ['index']]);
        $this->middleware('permission:crear-compra', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-compra', ['only' => ['show']]);
        $this->middleware('permission:eliminar-compra', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $compras = Compra::with('comprobante', 'proveedore.persona')
            ->where('estado', 1)
            ->latest()
            ->get();

        return view('compra.index', compact('compras'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $proveedores = Proveedore::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();
        $comprobantes = Comprobante::all();
        $productos = Producto::where('estado', 1)->get();
        return view('compra.create', compact('proveedores', 'comprobantes', 'productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompraRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Llenar tabla compras
            $compra = Compra::create($request->validated());

            // Registrar la actividad
            ActivityLogService::log('Compra creada', 'compras', [
                'comprobante_id' => $compra->comprobante_id,
                'proveedore_id' => $compra->proveedore_id,
                'fecha_compra' => $compra->fecha_compra,
                'total' => $compra->total,
            ]);

            // Llenar tabla compra_producto
            $arrayProducto_id = $request->get('arrayidproducto');
            $arrayCantidad = $request->get('arraycantidad');
            $arrayPrecioCompra = $request->get('arraypreciocompra');
            $arrayPrecioVenta = $request->get('arrayprecioventa');

            $sizeArray = count($arrayProducto_id);
            $cont = 0;
            while ($cont < $sizeArray) {
                $compra->productos()->syncWithoutDetaching([
                    $arrayProducto_id[$cont] => [
                        'cantidad' => $arrayCantidad[$cont],
                        'precio_compra' => $arrayPrecioCompra[$cont],
                        'precio_venta' => $arrayPrecioVenta[$cont]
                    ]
                ]);

                // Actualizar el stock
                $producto = Producto::find($arrayProducto_id[$cont]);
                $stockActual = $producto->stock;
                $stockNuevo = intval($arrayCantidad[$cont]);

                DB::table('productos')
                    ->where('id', $producto->id)
                    ->update([
                        'stock' => $stockActual + $stockNuevo
                    ]);

                $cont++;
            }

            DB::commit();

            return redirect()->route('compras.index')->with('success', 'Compra registrada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear la compra:', ['error' => $e->getMessage()]);
            return redirect()->route('compras.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Compra $compra): View
    {
        // Registrar la actividad
        ActivityLogService::log('Compra visualizada', 'compras', [
            'comprobante_id' => $compra->comprobante_id,
            'proveedore_id' => $compra->proveedore_id,
            'fecha_compra' => $compra->fecha_compra,
            'total' => $compra->total,
        ]);

        return view('compra.show', compact('compra'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $compra = Compra::findOrFail($id);

            // Eliminar lógicamente la compra
            $compra->update(['estado' => 0]);

            // Registrar la actividad
            ActivityLogService::log('Compra eliminada', 'compras', [
                'comprobante_id' => $compra->comprobante_id,
                'proveedore_id' => $compra->proveedore_id,
                'fecha_compra' => $compra->fecha_compra,
                'total' => $compra->total,
            ]);

            DB::commit();

            return redirect()->route('compras.index')->with('success', 'Compra eliminada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar la compra:', ['error' => $e->getMessage()]);
            return redirect()->route('compras.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }
}
