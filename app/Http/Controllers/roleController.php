<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class roleController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-role|crear-role|editar-role|eliminar-role', ['only' => ['index']]);
        $this->middleware('permission:crear-role', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-role', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-role', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $roles = Role::all();
        return view('role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $permisos = Permission::all();
        return view('role.create', compact('permisos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permission' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // Crear rol
            $rol = Role::create(['name' => $request->name]);

            // Asignar permisos
            $permisosAsignados = array_map(fn($value) => (int)$value, $request->permission);
            $rol->syncPermissions($permisosAsignados);

            // Filtrar datos relevantes para el registro de actividad
            $logData = [
                'nombre_rol' => $request->name,
                'permisos_asignados' => $permisosAsignados,
            ];

            // Registrar la actividad
            ActivityLogService::log('Rol creado', 'roles', $logData);

            DB::commit();

            return redirect()->route('roles.index')->with('success', 'Rol registrado');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear el rol:', ['error' => $e->getMessage()]);
            return redirect()->route('roles.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
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
    public function edit(Role $role): View
    {
        $permisos = Permission::all();
        return view('role.edit', compact('role', 'permisos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permission' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // Actualizar rol
            $role->update(['name' => $request->name]);

            // Actualizar permisos
            $permisosAsignados = array_map(fn($value) => (int)$value, $request->permission);
            $role->syncPermissions($permisosAsignados);

            // Filtrar datos relevantes para el registro de actividad
            $logData = [
                'nombre_rol' => $request->name,
                'permisos_asignados' => $permisosAsignados,
            ];

            // Registrar la actividad
            ActivityLogService::log('Rol actualizado', 'roles', $logData);

            DB::commit();

            return redirect()->route('roles.index')->with('success', 'Rol editado');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar el rol:', ['error' => $e->getMessage()]);
            return redirect()->route('roles.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($id);

            // Eliminar el rol
            $role->delete();

            // Registrar la actividad
            ActivityLogService::log('Rol eliminado', 'roles', [
                'nombre_rol' => $role->name,
            ]);

            DB::commit();

            return redirect()->route('roles.index')->with('success', 'Rol eliminado');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar el rol:', ['error' => $e->getMessage()]);
            return redirect()->route('roles.index')->with('error', 'Ups, algo salió mal. Por favor, inténtalo de nuevo.');
        }
    }
}
