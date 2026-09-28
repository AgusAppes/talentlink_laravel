<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalRrhh extends Model
{
    protected $table = 'personal_rrhh';

    public $timestamps = false;

    protected $fillable = [
        'usuarios_id',
        'nombre',
        'apellido',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuarios_id');
    }
}
