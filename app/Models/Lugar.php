<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategoriaLugar;

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
        'activo',          // Para eliminación lógica (true = existe, false = eliminado)
        'bloqueado',       // Para el toggle de estado (true = bloqueado, false = disponible)
        'motivo_bloqueo',  // Razón del bloqueo
        'fecha_bloqueo',   // Cuándo fue bloqueado
        'bloqueado_por',   // ID del admin que bloqueó
        'fecha_desbloqueo', // Cuándo fue desbloqueado
        'desbloqueado_por', // ID del admin que desbloqueó
        'id_usuario',
    ];

    protected $casts = [
        'dias_servicio' => 'array',
        'activo' => 'boolean',
        'bloqueado' => 'boolean',
        'horario_apertura' => 'datetime:H:i:s',
        'horario_cierre' => 'datetime:H:i:s',
        'fecha_bloqueo' => 'datetime',
        'fecha_desbloqueo' => 'datetime',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(CategoriaLugar::class, 'id_categoria');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'id_direccion');
    }

    public function imagenes()
    {
        return $this->hasMany(Imagenes::class, 'id_lugar');
    }

    // Relación con el admin que bloqueó
    public function adminBloqueador()
    {
        return $this->belongsTo(Usuario::class, 'bloqueado_por', 'id_usuario');
    }

    // Relación con el admin que desbloqueó
    public function adminDesbloqueador()
    {
        return $this->belongsTo(Usuario::class, 'desbloqueado_por', 'id_usuario');
    }

    // Scopes útiles
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeBloqueados($query)
    {
        return $query->where('bloqueado', true);
    }

    public function scopeDisponibles($query)
    {
        return $query->where('activo', true)->where('bloqueado', false);
    }

    // Accessors útiles
    public function getEstadoTextoAttribute()
    {
        if (!$this->activo) {
            return 'Eliminado';
        }
        
        if ($this->bloqueado) {
            return 'Bloqueado';
        }
        
        return 'Disponible';
    }

    public function getDiasServicioTextoAttribute()
    {
        if (is_array($this->dias_servicio)) {
            return implode(', ', $this->dias_servicio);
        }
        
        return $this->dias_servicio ?? 'No especificado';
    }

    public function getHorarioCompletoAttribute()
    {
        if ($this->horario_apertura && $this->horario_cierre) {
            return $this->horario_apertura->format('H:i') . ' - ' . $this->horario_cierre->format('H:i');
        }
        
        return 'No especificado';
    }

    public function getDiasDesdeBloqueoAttribute()
    {
        if ($this->fecha_bloqueo) {
            return $this->fecha_bloqueo->diffInDays(now());
        }
        
        return null;
    }
}