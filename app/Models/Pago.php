<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Pago extends Model
{
   protected $table = 'Pago';
   public $timestamps = false;


   protected $fillable = [
       'id_usuario',
       'id_lugar',
       'monto',
       'id_metodo_pago',
       'fecha_pago'
   ];


   public function usuario()
   {
       return $this->belongsTo(User::class, 'id_usuario','id_usuario');
   }


   public function lugar()
   {
       return $this->belongsTo(Lugar::class, 'id_lugar','id_lugar');
   }


   public function metodoPago()
   {
       return $this->belongsTo(MetodoPago::class, 'id_metodo_pago','id_metodo_pago');
   }
}


