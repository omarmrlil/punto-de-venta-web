<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venta extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

public function caja(): BelongsTo{
        return $this->belongsTo(Caja::class);
    }

    public function cliente(): BelongsTo{
        return $this->belongsTo(Cliente::class);
    }

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function comprobante() : BelongsTo{
        return $this->belongsTo(Comprobante::class);
    }

    public function productos(): BelongsToMany{
        return $this->belongsToMany(Producto::class)->withTimestamps()
        ->withPivot('cantidad','precio_venta');
    }
}
