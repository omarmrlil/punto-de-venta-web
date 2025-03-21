<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $guarded = ['id'];
    protected $table = ['inventario'];

public function ubicacione(){
        return $this->belongsTo(ubicacione::class);
    }

    public function producto(){
        return $this->belongsTo(Producto::class);
    }

}
