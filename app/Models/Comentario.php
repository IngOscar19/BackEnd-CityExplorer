<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comentario extends Model
{
    use HasFactory;

    protected $table = 'Comentario'; 

    protected $primaryKey = 'id_comentario'; 

    public $timestamps = false; 

    protected $fillable = [
        'contenido',
        'valoracion',
        'fecha_creacion',
        'id_usuario',
        'id_lugar',
    ];

    protected $casts = [
        'valoracion' => 'integer', // Cambiado de 'boolean' a 'integer' para valoraciones 1-5
        'fecha_creacion' => 'datetime',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function lugar()
    {
        return $this->belongsTo(Lugar::class, 'id_lugar');
    }

    // Scopes para filtros
    public function scopePorLugar($query, $idLugar)
    {
        return $query->where('id_lugar', $idLugar);
    }

    public function scopePorUsuario($query, $idUsuario)
    {
        return $query->where('id_usuario', $idUsuario);
    }

    // Funciones estáticas para estadísticas

    /**
     * Obtener promedio de valoración por lugar
     * @param int $idLugar
     * @return float
     */
    public static function promedioValoracionPorLugar($idLugar)
    {
        return self::where('id_lugar', $idLugar)->avg('valoracion') ?? 0;
    }

    /**
     * Obtener total de comentarios por lugar
     * @param int $idLugar
     * @return int
     */
    public static function totalComentariosPorLugar($idLugar)
    {
        return self::where('id_lugar', $idLugar)->count();
    }

    /**
     * Obtener estadísticas completas de un lugar
     * @param int $idLugar
     * @return object
     */
    public static function estadisticasCompletasLugar($idLugar)
    {
        return self::where('id_lugar', $idLugar)
                   ->selectRaw('
                       COALESCE(AVG(valoracion), 0) as promedio_valoracion,
                       COUNT(*) as total_comentarios,
                       MAX(valoracion) as valoracion_maxima,
                       MIN(valoracion) as valoracion_minima
                   ')
                   ->first();
    }

    /**
     * Obtener distribución de valoraciones por lugar
     * @param int $idLugar
     * @return \Illuminate\Support\Collection
     */
    public static function distribucionValoraciones($idLugar)
    {
        return self::where('id_lugar', $idLugar)
                   ->selectRaw('valoracion, COUNT(*) as cantidad')
                   ->groupBy('valoracion')
                   ->orderBy('valoracion')
                   ->get();
    }

    /**
     * Verificar si un lugar tiene comentarios
     * @param int $idLugar
     * @return bool
     */
    public static function lugarTieneComentarios($idLugar)
    {
        return self::where('id_lugar', $idLugar)->exists();
    }

    /**
     * Obtener promedio de valoración con número de comentarios mínimo
     * @param int $idLugar
     * @param int $minimoComentarios
     * @return float|null
     */
    public static function promedioConMinimo($idLugar, $minimoComentarios = 3)
    {
        $totalComentarios = self::totalComentariosPorLugar($idLugar);
        
        if ($totalComentarios < $minimoComentarios) {
            return null; // No hay suficientes comentarios para un promedio confiable
        }
        
        return self::promedioValoracionPorLugar($idLugar);
    }

    /**
     * Obtener comentarios recientes de un lugar
     * @param int $idLugar
     * @param int $limite
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function comentariosRecientes($idLugar, $limite = 5)
    {
        return self::with('usuario')
                   ->where('id_lugar', $idLugar)
                   ->orderBy('fecha_creacion', 'desc')
                   ->limit($limite)
                   ->get();
    }

    /**
     * Obtener estadísticas por valoración específica
     * @param int $idLugar
     * @param int $valoracion
     * @return int
     */
    public static function totalPorValoracion($idLugar, $valoracion)
    {
        return self::where('id_lugar', $idLugar)
                   ->where('valoracion', $valoracion)
                   ->count();
    }
}


