<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Rol extends Model {
   use HasFactory;


   protected $table = 'Rol';
   protected $primaryKey = 'id_rol';
   public $timestamps = false;


   protected $fillable = [
       'nombre',
       'descripcion'
   ];


   // Relación con Usuarios
   public function usuarios() {
       return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
   }
}


