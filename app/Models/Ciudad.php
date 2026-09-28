<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ciudad extends Model
{
    protected $table = 'ciudades';

    public $timestamps = false;

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'provincias_id');
    }
}
