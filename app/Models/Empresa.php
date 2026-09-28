<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    protected $table = 'empresas';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'usuarios_id',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuarios_id');
    }

    public function busquedas(): HasMany
    {
        return $this->hasMany(Busqueda::class, 'empresas_id');
    }
}
