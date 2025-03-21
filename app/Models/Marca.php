<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
USE Illuminate\Database\Eloquent\Relations\HasMany;
USE Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marca extends Model
{
    use HasFactory;

    public function productos(): HasMany{
        return $this->hasMany(Producto::class);
    }

    public function caracteristica(): BelongsTo {
        return $this->belongsTo(Caracteristica::class);
    }

    protected $fillable = ['caracteristica_id'];
}
