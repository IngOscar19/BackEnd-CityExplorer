<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Especificar la tabla personalizada
    protected $table = 'Usuario';
    
    // Especificar la clave primaria personalizada
    protected $primaryKey = 'id_usuario';
    
    // Especificar que Laravel no maneje timestamps automáticamente si no los tienes
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nombre',
        'apellidoP',
        'apellidoM', 
        'correo',
        'password',
        'foto_perfil',
        'id_rol',
        'activo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'activo' => 'boolean',
        'password' => 'hashed', 
    ];

    // Método para obtener el nombre completo
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellidoP . ' ' . $this->apellidoM);
    }

    // Método para el email (Laravel espera 'email', pero tienes 'correo')
    public function getEmailAttribute()
    {
        return $this->correo;
    }

    // Método para compatibilidad con Auth
    public function getAuthIdentifierName()
    {
        return 'correo'; // Laravel usará 'correo' en lugar de 'email'
    }

    // Relaciones
   public function rol()
   {
     return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
   }


   public function lugares()
   {
       return $this->hasMany(Lugar::class, 'id_usuario');
   }


   public function comentarios()
   {
       return $this->hasMany(Comentario::class, 'id_usuario');
   }


   public function favoritos()
   {
       return $this->hasMany(Favorito::class, 'id_usuario');
   }


   public function pagos()
   {
       return $this->hasMany(Pago::class, 'id_usuario');
   }


   public function listas()
   {
       return $this->hasMany(Lista::class, 'id_usuario');
   }

    // Scope para usuarios activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}