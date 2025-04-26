<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Lugar extends Model
{
   use HasFactory;


   protected $table = 'Lugar';


   protected $primaryKey = 'id_lugar';


   public $timestamps = false;


   protected $fillable = [
       'paginaWeb',
       'nombre',
       'descripcion',
       'dias_servicio',
       'num_telefonico',
       'horario_apertura',
       'horario_cierre',
       'id_categoria',
       'id_direccion',
       'activo',
       'url',
       'id_usuario',
   ];


   protected $casts = [
       'dias_servicio' => 'array',
       'activo' => 'boolean',
       'horario_apertura' => 'datetime:H:i:s',
       'horario_cierre' => 'datetime:H:i:s',
   ];


   // Relaciones
   public function categoria()
   {
       return $this->belongsTo(Categoria::class, 'id_categoria');
   }


   public function usuario()
   {
       return $this->belongsTo(Usuario::class, 'id_usuario');
   }


   public function direccion()
   {
       return $this->belongsTo(Direccion::class, 'id_direccion');
   }
}
