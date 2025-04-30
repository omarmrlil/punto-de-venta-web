<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaracteristicaRequest;
use App\Http\Requests\UpdateMarcaRequest;
use App\Models\Caracteristica;
use App\Models\Marca;
use App\Services\ActivityLogService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class marcaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-marca|crear-marca|editar-marca|eliminar-marca', ['only' => ['index']]);
        $this->middleware('permission:crear-marca', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-marca', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-marca', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $marcas = Marca::with('caracteristica')->latest()->get();
        return view('marca.index', compact('marcas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('marca.create');
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

            // Crear la marca asociada
            $marca = $caracteristica->marca()->create([
                'caracteristica_id' => $caracteristica->id,
            ]);

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->validated())->only(['nombre', 'descripcion'])->toArray();

            // Registrar la actividad
            ActivityLogService::log('Marca creada', 'marcas', $logData);

            DB::commit();

            return redirect()->route('marcas.index')->with('success', 'Marca registrada');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear la marca:', ['error' => $e->getMessage()]);
            return redirect()->route('marcas.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
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
    public function edit(Marca $marca): View
    {
        return view('marca.edit', compact('marca'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMarcaRequest $request, Marca $marca): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Actualizar la característica asociada a la marca
            $marca->caracteristica->update($request->validated());

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->validated())->only(['nombre', 'descripcion'])->toArray();

            // Registrar la actividad
            ActivityLogService::log('Marca actualizada', 'marcas', $logData);

            DB::commit();

            return redirect()->route('marcas.index')->with('success', 'Marca editada');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar la marca:', ['error' => $e->getMessage()]);
            return redirect()->route('marcas.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $marca = Marca::findOrFail($id);
            $message = '';

            if ($marca->caracteristica->estado == 1) {
                $marca->caracteristica->update(['estado' => 0]);
                $message = 'Marca eliminada';

                // Registrar la actividad
                ActivityLogService::log('Marca eliminada', 'marcas', [
                    'nombre' => $marca->caracteristica->nombre,
                    'accion' => 'Eliminación lógica',
                ]);
            } else {
                $marca->caracteristica->update(['estado' => 1]);
                $message = 'Marca restaurada';

                // Registrar la actividad
                ActivityLogService::log('Marca restaurada', 'marcas', [
                    'nombre' => $marca->caracteristica->nombre,
                    'accion' => 'Restauración lógica',
                ]);
            }

            DB::commit();

            return redirect()->route('marcas.index')->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar/restaurar la marca:', ['error' => $e->getMessage()]);
            return redirect()->route('marcas.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }
}
