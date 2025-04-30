<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaracteristicaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Caracteristica;
use App\Models\Categoria;
use App\Services\ActivityLogService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class categoriaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-categoria|crear-categoria|editar-categoria|eliminar-categoria', ['only' => ['index']]);
        $this->middleware('permission:crear-categoria', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-categoria', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-categoria', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categorias = Categoria::with('caracteristica')->latest()->get();
        return view('categoria.index', ['categorias' => $categorias]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('categoria.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCaracteristicaRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Crear la característica
            $caracteristica = Caracteristica::create($request->validated());

            // Crear la categoría asociada
            $categoria = $caracteristica->categoria()->create([
                'caracteristica_id' => $caracteristica->id,
            ]);

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->validated())->only(['nombre', 'descripcion'])->toArray();

            // Registrar la actividad
            ActivityLogService::log('Categoría creada', 'categorias', $logData);

            DB::commit();

            return redirect()->route('categorias.index')->with('success', 'Categoría registrada');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear la categoría:', ['error' => $e->getMessage()]);
            return redirect()->route('categorias.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
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
    public function edit(Categoria $categoria): View
    {
        return view('categoria.edit', ['categoria' => $categoria]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Actualizar la característica asociada
            $categoria->caracteristica->update($request->validated());

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->validated())->only(['nombre', 'descripcion'])->toArray();

            // Registrar la actividad
            ActivityLogService::log('Categoría actualizada', 'categorias', $logData);

            DB::commit();

            return redirect()->route('categorias.index')->with('success', 'Categoría editada');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar la categoría:', ['error' => $e->getMessage()]);
            return redirect()->route('categorias.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $categoria = Categoria::findOrFail($id);
            $message = '';

            if ($categoria->caracteristica->estado == 1) {
                $categoria->caracteristica->update(['estado' => 0]);
                $message = 'Categoría eliminada';

                // Registrar la actividad
                ActivityLogService::log('Categoría eliminada', 'categorias', [
                    'nombre' => $categoria->caracteristica->nombre,
                    'accion' => 'Eliminación lógica',
                ]);
            } else {
                $categoria->caracteristica->update(['estado' => 1]);
                $message = 'Categoría restaurada';

                // Registrar la actividad
                ActivityLogService::log('Categoría restaurada', 'categorias', [
                    'nombre' => $categoria->caracteristica->nombre,
                    'accion' => 'Restauración lógica',
                ]);
            }

            DB::commit();

            return redirect()->route('categorias.index')->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar/restaurar la categoría:', ['error' => $e->getMessage()]);
            return redirect()->route('categorias.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }
}
