<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Direccion extends Model {
   use HasFactory;


   protected $table = 'Direccion';
   protected $primaryKey = 'id_direccion';
   public $timestamps = false;


   protected $fillable = [
       'calle',
       'numero_int',
       'numero_ext',
       'colonia',
       'codigo_postal'
   ];
}


