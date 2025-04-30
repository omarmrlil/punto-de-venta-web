<?php

namespace App\Http\Controllers;

use App\Enums\TipoPersonaEnum;
use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateProveedoreRequest;
use App\Models\Documento;
use App\Models\Persona;
use App\Models\Proveedore;
use App\Services\ActivityLogService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class proveedorController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-proveedore|crear-proveedore|editar-proveedore|eliminar-proveedore', ['only' => ['index']]);
        $this->middleware('permission:crear-proveedore', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-proveedore', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-proveedore', ['only' => ['destroy']]);
    }

 public function index(): View
{
    $proveedores = Proveedore::with(['persona.documento'])->latest()->get();
    return view('proveedore.index', compact('proveedores'));
}
    public function create(): View
    {
        $documentos = Documento::all();
        $optionsTipopersona = TipoPersonaEnum::cases();
        return view('proveedore.create', compact('documentos', 'optionsTipopersona'));
    }

    public function store(StorePersonaRequest $request): RedirectResponse
{
    try {
        DB::beginTransaction();

        // Asignar valores por defecto si los campos están vacíos
        $data = $request->validated();
        $data['direccion'] = $data['direccion'] ?? null;
        $data['telefono'] = $data['telefono'] ?? null;
        $data['email'] = $data['email'] ?? null;

        // Crear la persona
        $persona = Persona::create($data);

        // Crear el proveedor asociado
        $persona->proveedore()->create([]);

        DB::commit();
// Registrar la actividad
        ActivityLogService::log('Proveedor creado', 'proveedores', $request->validated());
    return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado');

    } catch (Throwable $e) {
        DB::rollBack();
        Log::error('Error al crear el proveedor:', ['error' => $e->getMessage()]);
        return redirect()->route('proveedores.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
    }
}
    public function edit(Proveedore $proveedore): View
    {
        $proveedore->load('persona.documento');
        $documentos = Documento::all();
        return view('proveedore.edit', compact('proveedore', 'documentos'));
    }

    public function update(UpdateProveedoreRequest $request, Proveedore $proveedore): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Actualizar la persona asociada al proveedor
            $proveedore->persona->update($request->validated());

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->validated())->only(['razon_social', 'direccion', 'telefono', 'tipo', 'email', 'documento_id', 'numero_documento'])->toArray();

            // Registrar la actividad
            ActivityLogService::log('Proveedor actualizado', 'proveedores', $logData);

            DB::commit();

            return redirect()->route('proveedores.index')->with('success', 'Proveedor editado');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar el proveedor:', ['error' => $e->getMessage()]);
            return redirect()->route('proveedores.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $persona = Persona::findOrFail($id);
            $message = '';

            if ($persona->estado == 1) {
                $persona->update(['estado' => 0]);
                $message = 'Proveedor eliminado';

                // Registrar la actividad
                ActivityLogService::log('Proveedor eliminado', 'proveedores', [
                    'razon_social' => $persona->razon_social,
                    'accion' => 'Eliminación lógica',
                ]);
            } else {
                $persona->update(['estado' => 1]);
                $message = 'Proveedor restaurado';

                // Registrar la actividad
                ActivityLogService::log('Proveedor restaurado', 'proveedores', [
                    'razon_social' => $persona->razon_social,
                    'accion' => 'Restauración lógica',
                ]);
            }

            DB::commit();

            return redirect()->route('proveedores.index')->with('success', $message);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error al eliminar/restaurar el proveedor:', ['error' => $e->getMessage()]);
            return redirect()->route('proveedores.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
}
}

