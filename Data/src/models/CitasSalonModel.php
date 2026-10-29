<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitasSalonModel extends Model
{

  //tabla de la base de datos
  protected $table = 'citas_salon';
  //la tabla no tiene updated_at, created_at lo llena MySQL
  public $timestamps = false;
  // campos que se pueden asignar masivamente
  protected $fillable = [

    'nombre_cliente',
    'telefono_cliente',
    'servicio_solicitado',
    'estilista_asignado',
    'fecha_hora_cita',
    'precio_servicio',
    'estado',

  ];

  protected $casts = [
    'id' => 'integer',
    'precio_servicio' => 'float',
  ];

}
