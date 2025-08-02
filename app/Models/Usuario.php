<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'Usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'apellidoP',
        'apellidoM',
        'correo',
        'password',
        'id_rol',
        'activo',
        'foto_perfil',
        'fecha_bloqueo',
        'motivo_bloqueo',
        'bloqueado_por',
        'fecha_desbloqueo',
        'desbloqueado_por',
        'last_login',
        'bloqueado'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_bloqueo' => 'datetime',
        'fecha_desbloqueo' => 'datetime',
        'last_login' => 'datetime',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'bloqueado' => 'boolean',
    ];

    // Accessors
    public function getDiasDesdeBloqueoAttribute()
    {
        if (!$this->fecha_bloqueo) {
            return null;
        }
        
        return $this->fecha_bloqueo->diffInDays(now());
    }

    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellidoP . ' ' . ($this->apellidoM ?? ''));
    }

    public function getEstadoTextoAttribute()
    {
        if ($this->bloqueado) {
            return 'Bloqueado';
        }
        
        return $this->activo ? 'Activo' : 'Inactivo';
    }

    public function getFotoPerfilUrlAttribute()
    {
        if ($this->foto_perfil) {
            return asset('storage/' . $this->foto_perfil);
        }
        return asset('images/default-avatar.png'); // Avatar por defecto
    }

    // Relaciones
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function lugares()
    {
        return $this->hasMany(Lugar::class, 'id_usuario', 'id_usuario');
    }

    // Métodos útiles
    public function esAdministrador()
    {
        return $this->rol && 
               (strcasecmp($this->rol->nombre, 'administrador') === 0 || 
                strcasecmp($this->rol->nombre, 'Administrador') === 0);
    }

    public function actualizarUltimoLogin()
    {
        $this->update(['last_login' => now()]);
    }

    public function bloquear($motivo = null, $bloqueadoPor = null)
    {
        $this->update([
            'bloqueado' => true,
            'fecha_bloqueo' => now(),
            'motivo_bloqueo' => $motivo ?? 'Bloqueado por administrador',
            'bloqueado_por' => $bloqueadoPor,
            'fecha_desbloqueo' => null,
            'desbloqueado_por' => null
        ]);
        
        // Revocar tokens
        $this->tokens()->delete();
    }

    public function desbloquear($desbloqueadoPor = null)
    {
        $this->update([
            'bloqueado' => false,
            'fecha_bloqueo' => null,
            'motivo_bloqueo' => null,
            'bloqueado_por' => null,
            'fecha_desbloqueo' => now(),
            'desbloqueado_por' => $desbloqueadoPor
        ]);
    }

    // Scopes para búsquedas
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre', 'like', '%' . $termino . '%')
              ->orWhere('apellidoP', 'like', '%' . $termino . '%')
              ->orWhere('apellidoM', 'like', '%' . $termino . '%')
              ->orWhere('correo', 'like', '%' . $termino . '%');
        });
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeInactivos($query)
    {
        return $query->where('activo', false);
    }

    public function scopeBloqueados($query)
    {
        return $query->where('bloqueado', true);
    }

    public function scopeNoBloqueados($query)
    {
        return $query->where('bloqueado', false);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)->where('bloqueado', false);
    }

    // Override del método de autenticación para verificar si está bloqueado
    public function canAccessTokens()
    {
        return $this->activo && !$this->bloqueado;
    }
}