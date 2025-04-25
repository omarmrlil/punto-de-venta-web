<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activitylog extends Model
{
protected $table = 'activity_logs'; // Asegúrate de que coincida con el nombre de la tabla

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'data',
        'ip_address',
    ];

    protected $casts = [
        'data' => 'array', // Convierte automáticamente el campo `data` a array
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
