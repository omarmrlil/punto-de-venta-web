<?php

namespace App\Http\Middleware;

use App\Models\Producto;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProductoInicializado
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $producto = Producto::findOrFail($request->producto_id);
        if($producto->estado == 1) {
            return redirect()->route('productos.index')->with('error', 'El producto ya fue inicializado');
        }
        return $next($request);
    }
}
