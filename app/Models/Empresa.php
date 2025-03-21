<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $guarded = ['id'];

    protected $table = 'empresa';

public function moneda(){
        return $this->belongsTo(Moneda::class);
    }

}
