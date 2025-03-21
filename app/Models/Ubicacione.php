<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacione extends Model
{

    public function inventarios(){
        return $this->hasMany(Inventario::class);
    }
}
