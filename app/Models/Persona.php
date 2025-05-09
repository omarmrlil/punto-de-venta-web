<?php

namespace App\Models;

use App\Enums\TipoPersonaEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $cast = [
        'tipo' => TipoPersonaEnum::class
    ];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }

    public function proveedore(): HasOne
    {
        return $this->hasOne(Proveedore::class);
    }
     public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }
}
