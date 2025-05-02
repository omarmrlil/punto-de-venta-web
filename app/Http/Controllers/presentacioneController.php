<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaracteristicaRequest;
use App\Http\Requests\UpdatePresentacioneRequest;
use App\Models\Caracteristica;
use App\Models\Presentacione;
use App\Services\ActivityLogService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class presentacioneController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-presentacione|crear-presentacione|editar-presentacione|eliminar-presentacione', ['only' => ['index']]);
        $this->middleware('permission:crear-presentacione', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-presentacione', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-presentacione', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $presentaciones = Presentacione::with('caracteristica')->latest()->get();
        return view('presentacione.index', compact('presentaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('presentacione.create');
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

            // Crear la presentación asociada
            $caracteristica->presentacione()->create(['sigla' => $request->sigla]);

            DB::commit();

            ActivityLogService::log('Creación de presentación', 'Presentaciones', $request->validated());

            return redirect()->route('presentaciones.index')->with('success', 'Presentación registrada');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear la presentación:', ['error' => $e->getMessage()]);
            return redirect()->route('presentaciones.index')->with('error', 'Ups, algo salió mal.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Presentacione $presentacione): View
    {
        // Cargar la relación con característica
        $presentacione->load('caracteristica');
        return view('presentacione.edit', compact('presentacione'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePresentacioneRequest $request, Presentacione $presentacione): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Actualizar la característica asociada
            $presentacione->caracteristica->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
            ]);

            // Actualizar la presentación
            $presentacione->update(['sigla' => $request->sigla]);

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->all())->only(['nombre', 'descripcion', 'sigla'])->toArray();

            // Registrar la actividad
            ActivityLogService::log('Presentación actualizada', 'presentaciones', $logData);

            DB::commit();

            return redirect()->route('presentaciones.index')->with('success', 'Presentación editada');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar la presentación:', ['error' => $e->getMessage()]);
            return redirect()->route('presentaciones.index')->with('error', 'Ups, algo salió mal.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $presentacione = Presentacione::findOrFail($id);
            $message = '';

            if ($presentacione->caracteristica->estado == 1) {
                $presentacione->caracteristica->update(['estado' => 0]);
                $message = 'Presentación eliminada';

                // Registrar la actividad
                ActivityLogService::log('Presentación eliminada', 'presentaciones', [
                    'nombre' => $presentacione->caracteristica->nombre,
                    'accion' => 'Eliminación lógica',
                ]);
            } else {
                $presentacione->caracteristica->update(['estado' => 1]);
                $message = 'Presentación restaurada';

                // Registrar la actividad
                ActivityLogService::log('Presentación restaurada', 'presentaciones', [
                    'nombre' => $presentacione->caracteristica->nombre,
                    'accion' => 'Restauración lógica',
                ]);
            }

            DB::commit();

            return redirect()->route('presentaciones.index')->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar/restaurar la presentación:', ['error' => $e->getMessage()]);
            return redirect()->route('presentaciones.index')->with('error', 'Ups, algo salió mal.');
        }
    }
}
