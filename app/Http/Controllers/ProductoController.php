<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Presentacione;
use App\Models\Producto;
use App\Services\ActivityLogService;
use App\Services\ProductoService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Constructor: Define los permisos necesarios para cada método.
     */
    protected $productoService;

    public function __construct(ProductoService $productoService)
    {
        $this->productoService = $productoService;

        // Define los permisos para cada método
        $this->middleware('permission:ver-producto|crear-producto|editar-producto|eliminar-producto', ['only' => ['index']]);
        $this->middleware('permission:crear-producto', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-producto', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-producto', ['only' => ['destroy']]);
    }

    /**
     * Muestra una lista de productos con sus relaciones cargadas.
     */
    public function index(): View
    {
        $productos = Producto::with([
            'marca.caracteristica',
            'presentacione.caracteristica',
            'categoria.caracteristica'
        ])->latest()->get();

        return view('producto.index', compact('productos'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create(): View
    {
        // Consulta las marcas activas con su característica asociada
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->select('marcas.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1) // Solo marcas con estado activo
            ->get();

        // Consulta las presentaciones activas con su característica asociada
        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->select('presentaciones.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1) // Solo presentaciones con estado activo
            ->get();

        // Consulta las categorías activas con su característica asociada
        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->select('categorias.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1) // Solo categorías con estado activo
            ->get();

        return view('producto.create', compact('marcas', 'presentaciones', 'categorias'));
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(StoreProductoRequest $request, ProductoService $productoService): RedirectResponse
    {
        try {
            // Llama al servicio para crear el producto
            $productoService->crearProducto($request->validated());

            // Registra la actividad
            ActivityLogService::log('Creación de producto', 'Productos', $request->validated());

            return redirect()->route('productos.index')->with('success', 'Producto registrado');
        } catch (\Throwable $e) {
            // Loggea el error en caso de fallo
            Log::error('Error al crear el producto:', [
                'error' => $e->getMessage(),
                'data' => $request->validated(),
            ]);

            return redirect()->route('productos.index')->with('error', 'Ups, algo falló');
        }
    }

    /**
     * Muestra el formulario para editar un producto existente.
     */
    public function edit(Producto $producto): View
    {
        // Consulta las marcas activas con su característica asociada
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->select('marcas.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1) // Solo marcas con estado activo
            ->get();

        // Consulta las presentaciones activas con su característica asociada
        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->select('presentaciones.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1) // Solo presentaciones con estado activo
            ->get();

        // Consulta las categorías activas con su característica asociada
        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->select('categorias.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1) // Solo categorías con estado activo
            ->get();

        return view('producto.edit', compact('producto', 'marcas', 'presentaciones', 'categorias'));
    }

    /**
     * Actualiza un producto existente en la base de datos.
     */
    public function update(Request $request, Producto $producto): RedirectResponse
    {
        try {
            // Iniciar transacción para manejar errores
            DB::beginTransaction();

            // Validar los datos del formulario
            $validated = $request->validate([
                'codigo' => 'required|string|max:50',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'img_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Máximo 2MB
                'marca_id' => 'nullable|exists:marcas,id',
                'categoria_id' => 'nullable|exists:categorias,id',
                'presentacione_id' => 'nullable|exists:presentaciones,id',
            ]);

            // Llamar al método del servicio para editar el producto
            $this->productoService->editarProducto($validated, $producto);

            // Confirmar la transacción
            DB::commit();

            // Redirigir con mensaje de éxito
            return redirect()->route('productos.index')->with('success', 'Producto editado correctamente.');
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();

            // Registrar el error en los logs
            Log::error('Error al actualizar el producto:', ['error' => $e->getMessage()]);

            // Redirigir con mensaje de error
            return redirect()->route('productos.index')->with('error', 'Ups, algo salió mal al actualizar el producto.');
        }
    }

    /**
     * Elimina o restaura un producto (eliminación lógica).
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Busca el producto por ID
            $producto = Producto::findOrFail($id);

            // Determinar si se elimina o restaura
            $message = '';
            if ($producto->estado == 1) {
                $producto->update(['estado' => 0]); // Cambia el estado a inactivo
                $message = 'Producto eliminado';

                // Registra la actividad
                ActivityLogService::log('Producto eliminado', 'productos', [
                    'nombre' => $producto->nombre,
                    'accion' => 'Eliminación lógica',
                ]);
            } else {
                $producto->update(['estado' => 1]); // Restaura el producto
                $message = 'Producto restaurado';

                // Registra la actividad
                ActivityLogService::log('Producto restaurado', 'productos', [
                    'nombre' => $producto->nombre,
                    'accion' => 'Restauración lógica',
                ]);
            }

            DB::commit();

            return redirect()->route('productos.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar el error en los logs
            Log::error('Error al eliminar/restaurar el producto:', ['error' => $e->getMessage()]);

            return redirect()->route('productos.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }
}
