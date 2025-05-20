<?php

namespace App\Observers;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Ubicacione;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;


class InventarioObserver
{
    /**
     * Handle the Inventario "created" event.
     */
    public function created(Inventario $inventario): void
    {
        try {
            // Busca el producto relacionado
            $producto = Producto::find($inventario->producto_id);

            if ($producto && $producto->estado != 1) {
                // Actualiza el estado del producto a activo
                $producto->update(['estado' => 1]);
            }
        } catch (\Throwable $e) {
            // Registra el error en los logs
            Log::error('Error al actualizar el estado del producto:', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the Inventario "updated" event.
     */
    public function save(Inventario $inventario): void
    {
        $producto = Producto::where('id', $inventario->producto_id)->first();
        $producto->update([
        'estado' => 1
        ]);
    }

    /**
     * Handle the Inventario "updated" event.
     */
    public function updated(Inventario $inventario): void
    {
        //
    }

    /**
     * Handle the Inventario "deleted" event.
     */
    public function deleted(Inventario $inventario): void
    {
        //
    }

    /**
     * Handle the Inventario "restored" event.
     */
    public function restored(Inventario $inventario): void
    {
        //
    }

    /**
     * Handle the Inventario "force deleted" event.
     */
    public function forceDeleted(Inventario $inventario): void
    {
        //
    }
}
