<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;


    protected $fillable = ['persona_id'];

    public function ventas(): HasMany{
        return $this->hasMany(Venta::class);
    }

     public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

}
