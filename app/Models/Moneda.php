<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Moneda extends Model
{
    public function Empresa(): HasOne {
        return $this->hasOne(Empresa::class);
    }
}
