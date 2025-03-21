<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Producto extends Model
{
    use HasFactory;
protected $guarded = ['id'];

    public function kardex(): HasMany
    {
        return $this->hasMany(Kardex::class);
    }
    public function inventarios(): HasOne
    {
        return $this->hasOne(Inventario::class);
    }

    public function compras(): BelongsToMany
    {
        return $this->belongsToMany(Compra::class)->withTimestamps()
            ->withPivot('cantidad', 'precio_compra', 'fecha_vencimiento');
    }

    public function ventas(): BelongsTo
    {
        return $this->belongsTo(Venta::class)->withTimestamps()
            ->withPivot('cantidad', 'precio_venta');
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class)->withTimestamps();
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    public function presentacione(): BelongsTo
    {
        return $this->belongsTo(Presentacione::class);
    }

    public function handleUploadImage($image): string
    {
        $file = $image;
        $name = time() . $file->getClientOriginalName();
        //$file->move(public_path() . '/img/productos/', $name);
        Storage::putFileAs('/public/productos/',$file,$name,'public');

        return $name;
    }
}
