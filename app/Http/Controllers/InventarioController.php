<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Ubicacione;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\Inventario;
use App\Http\Requests\StoreInventarioRequest;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;
use Throwable;


class InventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    function __construct()
{
    $this->middleware('check_producto_inicializado', ['only' => ['create', 'store']]);
}
     public function index():View
    {
        $inventario = Inventario::latest()->get();
return view('inventario.index',compact('inventario'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
{
    // Validar que se haya proporcionado un producto_id
    $request->validate([
        'producto_id' => 'required|exists:productos,id',
    ]);

    $producto = Producto::findOrFail($request->producto_id);

    $ubicaciones = Ubicacione::all();

    return view('inventario.create', compact('producto', 'ubicaciones'));
}
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventarioRequest $request)
    {
        try {
            Inventario::create($request->validated());

            ActivityLogService::log('Inicialización de producto', 'Productos', $request->validated());
            return redirect()->route('productos.index')->with('success', 'Producto inicializado');
        } catch (Throwable $e) {
            Log::error('Error al inicializar el producto', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Producto no pudo ser inicializado');
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
