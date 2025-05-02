<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use App\Services\ActivityLogService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Enums\TipoPersonaEnum;

class clienteController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-cliente|crear-cliente|editar-cliente|eliminar-cliente', ['only' => ['index']]);
        $this->middleware('permission:crear-cliente', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-cliente', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-cliente', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */public function index(): View
{
    $clientes = Cliente::with(['persona.documento'])->latest()->get();
    return view('cliente.index', compact('clientes'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $documentos = Documento::all();
        $optionsTipopersona = TipoPersonaEnum::cases();
        return view('Cliente.create', compact('documentos', 'optionsTipopersona'));
    }


    /**
     * Store a newly created resource in storage.
     */
   public function store(StorePersonaRequest $request): RedirectResponse
{
    try {
        DB::beginTransaction();

        // Crear la persona
        $persona = Persona::create($request->validated());

        // Crear el cliente asociado
        $cliente = $persona->cliente()->create([
            'persona_id' => $persona->id,
        ]);

        // Filtrar datos relevantes para el registro de actividad
        $logData = collect($request->validated())->only([
            'razon_social', 'direccion', 'telefono', 'tipo', 'email', 'documento_id', 'numero_documento'
        ])->toArray();

        // Registrar la actividad
        if (class_exists(ActivityLogService::class)) {
            ActivityLogService::log('Cliente creado', 'clientes', $logData);
        }

        DB::commit();

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado');

    } catch (Exception $e) {
        DB::rollBack();
        Log::error('Error al crear el cliente:', ['error' => $e->getMessage()]);
        return redirect()->route('clientes.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
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
    public function edit(Cliente $cliente): View
    {
        $cliente->load('persona.documento');
        $documentos = Documento::all();
        return view('cliente.edit', compact('cliente', 'documentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Actualizar la persona asociada al cliente
            $cliente->persona->update($request->validated());

            // Filtrar datos relevantes para el registro de actividad
            $logData = collect($request->validated())->only([
                'razon_social', 'direccion', 'telefono', 'tipo', 'email', 'documento_id', 'numero_documento'
            ])->toArray();

            // Registrar la actividad
            if (class_exists(ActivityLogService::class)) {
                ActivityLogService::log('Cliente actualizado', 'clientes', $logData);
            }

            DB::commit();

            return redirect()->route('cliente.index')->with('success', 'Cliente editado');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar el cliente:', ['error' => $e->getMessage()]);
            return redirect()->route('cliente.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $persona = Persona::findOrFail($id);
            $message = '';

            if ($persona->estado == 1) {
                $persona->update(['estado' => 0]);
                $message = 'Cliente eliminado';

                // Registrar la actividad
                if (class_exists(ActivityLogService::class)) {
                    ActivityLogService::log('Cliente eliminado', 'clientes', [
                        'razon_social' => $persona->razon_social,
                        'accion' => 'Eliminación lógica',
                    ]);
                }
            } else {
                $persona->update(['estado' => 1]);
                $message = 'Cliente restaurado';

                // Registrar la actividad
                if (class_exists(ActivityLogService::class)) {
                    ActivityLogService::log('Cliente restaurado', 'clientes', [
                        'razon_social' => $persona->razon_social,
                        'accion' => 'Restauración lógica',
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('clientes.index')->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar/restaurar el cliente:', ['error' => $e->getMessage()]);
            return redirect()->route('clientes.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }
}
