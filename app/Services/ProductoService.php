<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Storage;


class ProductoService
{
    /**
     * Crear un nuevo registro de producto.
     *
     * @param array $data Datos del producto a crear.
     * @return Producto Retorna el producto creado.
     * @throws \Exception Si ocurre un error durante la creación del producto.
     */
    public function crearProducto(array $data): Producto
    {
        try {
            // Crear el registro en la tabla productos
            $producto = Producto::create([
                'codigo' => $data['codigo'],
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'img_path' => isset($data['img_path']) && $data['img_path']
                    ? $this->handleUploadImage($data['img_path'])
                    : null,
                'categoria_id' => $data['categoria_id'] ?? null,
                'marca_id' => $data['marca_id'],
                'presentacione_id' => $data['presentacione_id'],
            ]);

            // Retornar el producto creado
            return $producto;
        } catch (Throwable $e) {
            // Registrar el error en los logs
            Log::error('Error al crear el producto:', ['error' => $e->getMessage()]);

            // Relanzar la excepción para que el controlador la maneje
            throw new \Exception('Error al crear el producto: ' . $e->getMessage());
        }
    }

  public function editarProducto(array $data, Producto $producto)
{
    $producto->update([
        'codigo' => $data['codigo'],
        'nombre' => $data['nombre'],
        'descripcion' => $data['descripcion'],
        'img_path' => isset($data['img_path']) && $data['img_path']
            ? $this->handleUploadImage($data['img_path'], $producto->img_path)
            : $producto->img_path,
        'marca_id' => $data['marca_id'],
        'categoria_id' => $data['categoria_id'],
        'presentacione_id' => $data['presentacione_id'],
    ]);

    return $producto;
}

    /**
     * Manejar la carga y almacenamiento de una imagen.
     *
     * @param \Illuminate\Http\UploadedFile $image Archivo de imagen a procesar.
     * @return string Nombre del archivo almacenado.
     * @throws \Exception Si ocurre un error durante el procesamiento.
     */
private function handleUploadImage($image, string $img_path = null): string
{
    if ($img_path) {
        $relative_path = str_replace('storage/', '', $img_path);

        if (Storage::disk('public')->exists($relative_path)) {
            Storage::disk('public')->delete($relative_path);
        }
    }

    $name = uniqid() . '.' . $image->getClientOriginalExtension();
    $path = 'storage/' . $image->storeAs('productos', $name);

    return $path;
}
}
