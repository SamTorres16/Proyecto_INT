<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActExtraescolar extends Model
{
    use HasFactory;

    protected $table = 'act_extraesc'; 

    protected $primaryKey = 'id_act';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'activo',
        'horario',
        'lugar',
        'materiales',
        'cupo_masculino',
        'cupo_femenino',
        'no_empleado',
        'inscripcion_abierta',
        'parcial1_cerrado',
        'parcial2_cerrado',
        'parcial3_cerrado',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'no_empleado', 'no_empleado');
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'actividad_extraescolar', 'id_act');
    }

    public function getRouteKeyName()
    {
        return 'id_act';
    }
}
