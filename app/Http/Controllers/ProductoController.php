<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Presentacione;
use App\Models\Producto;
use App\Services\ActivityLogService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class ProductoController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-producto|crear-producto|editar-producto|eliminar-producto', ['only' => ['index']]);
        $this->middleware('permission:crear-producto', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-producto', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-producto', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $productos = Producto::with(['categorias.caracteristica', 'marca.caracteristica', 'presentacione.caracteristica'])->latest()->get();
        return view('producto.index', compact('productos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->select('marcas.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->select('presentaciones.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->select('categorias.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        return view('producto.create', compact('marcas', 'presentaciones', 'categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Tabla producto
            $producto = new Producto();
            if ($request->hasFile('img_path')) {
                $name = $producto->handleUploadImage($request->file('img_path'));
            } else {
                $name = null;
            }

            $producto->fill([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'img_path' => $name,
                'marca_id' => $request->marca_id,
                'presentacione_id' => $request->presentacione_id
            ]);

            $producto->save();

            // Tabla categoría producto
            $categorias = $request->get('categorias');
            $producto->categorias()->attach($categorias);

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->validated())->only(['codigo', 'nombre', 'descripcion', 'fecha_vencimiento', 'marca_id', 'presentacione_id', 'categorias'])->toArray();

            // Registrar la actividad
            ActivityLogService::log('Producto creado', 'productos', $logData);

            DB::commit();

            return redirect()->route('productos.index')->with('success', 'Producto registrado');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear el producto:', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
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
    public function edit(Producto $producto): View
    {
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->select('marcas.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->select('presentaciones.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->select('categorias.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();

        return view('producto.edit', compact('producto', 'marcas', 'presentaciones', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        try {
            DB::beginTransaction();

            if ($request->hasFile('img_path')) {
                $name = $producto->handleUploadImage($request->file('img_path'));

                // Eliminar si existiese una imagen
                if (Storage::disk('public')->exists('productos/' . $producto->img_path)) {
                    Storage::disk('public')->delete('productos/' . $producto->img_path);
                }
            } else {
                $name = $producto->img_path;
            }

            $producto->fill([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'img_path' => $name,
                'marca_id' => $request->marca_id,
                'presentacione_id' => $request->presentacione_id
            ]);

            $producto->save();

            // Tabla categoría producto
            $categorias = $request->get('categorias');
            $producto->categorias()->sync($categorias);

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->validated())->only(['codigo', 'nombre', 'descripcion', 'fecha_vencimiento', 'marca_id', 'presentacione_id', 'categorias'])->toArray();

            // Registrar la actividad
            ActivityLogService::log('Producto actualizado', 'productos', $logData);

            DB::commit();

            return redirect()->route('productos.index')->with('success', 'Producto editado');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar el producto:', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $producto = Producto::findOrFail($id);
            $message = '';

            if ($producto->estado == 1) {
                $producto->update(['estado' => 0]);
                $message = 'Producto eliminado';

                // Registrar la actividad
                ActivityLogService::log('Producto eliminado', 'productos', [
                    'nombre' => $producto->nombre,
                    'accion' => 'Eliminación lógica',
                ]);
            } else {
                $producto->update(['estado' => 1]);
                $message = 'Producto restaurado';

                // Registrar la actividad
                ActivityLogService::log('Producto restaurado', 'productos', [
                    'nombre' => $producto->nombre,
                    'accion' => 'Restauración lógica',
                ]);
            }

            DB::commit();

            return redirect()->route('productos.index')->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar/restaurar el producto:', ['error' => $e->getMessage()]);
            return redirect()->route('productos.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }
}
