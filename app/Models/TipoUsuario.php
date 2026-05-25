<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    use HasFactory;

    protected $table = 'tipo_usuario';     // Nombre de la tabla
    protected $primaryKey = 'id_tipo';     // PK
    public $timestamps = false;            // La tabla no usa timestamps

    protected $fillable = [
        'descripcion',
        'activo',
    ];
}
