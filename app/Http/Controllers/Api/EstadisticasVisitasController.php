<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EstadisticasVisitasController extends Controller
{
    /**
     * Registrar una nueva visita
     */
    public function registrarVisita(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_lugar' => 'required|integer|exists:Lugar,id_lugar',
                'tiempo_visita' => 'required|integer|min:1',
                'id_usuario' => 'nullable|integer|exists:Usuario,id_usuario'
            ]);

            $fecha = Carbon::now();

            DB::table('estadisticas_visitas')->insert([
                'id_lugar' => $validated['id_lugar'],
                'id_usuario' => $validated['id_usuario'] ?? null,
                'tiempo_visita' => $validated['tiempo_visita'],
                'fecha' => $fecha,
                'fecha_dia' => $fecha->toDateString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visita registrada correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar visita', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la visita'
            ], 500);
        }
    }

    /**
     * Obtener SOLO el tiempo promedio de visitas por lugar
     */
    public function obtenerTiempoPromedioLugar($idLugar)
    {
        try {
            $tiempoPromedio = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio'),
                    DB::raw('COUNT(*) as total_visitas_consideradas'),
                    DB::raw('SUM(tiempo_visita) as tiempo_total_acumulado')
                )
                ->where('id_lugar', $idLugar)
                ->first();

            // Verificar si hay visitas registradas
            if ($tiempoPromedio->total_visitas_consideradas == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay visitas registradas para este lugar'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id_lugar' => $idLugar,
                    'tiempo_promedio' => $tiempoPromedio->tiempo_promedio,
                    'total_visitas_consideradas' => $tiempoPromedio->total_visitas_consideradas,
                    'tiempo_total_acumulado' => $tiempoPromedio->tiempo_total_acumulado
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tiempo promedio por lugar', [
                'error' => $e->getMessage(),
                'id_lugar' => $idLugar
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el tiempo promedio de visitas'
            ], 500);
        }
    }

    /**
     * Obtener SOLO la cantidad de visitas por lugar
     */
    public function obtenerCantidadVisitasLugar($idLugar)
    {
        try {
            $cantidadVisitas = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('COUNT(DISTINCT id_usuario) as usuarios_unicos'),
                    DB::raw('COUNT(CASE WHEN id_usuario IS NULL THEN 1 END) as visitas_anonimas')
                )
                ->where('id_lugar', $idLugar)
                ->first();

            // Obtener visitas por día (últimos 30 días) para análisis de tendencia
            $visitasPorDia = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('DATE(fecha) as fecha'),
                    DB::raw('COUNT(*) as visitas_del_dia')
                )
                ->where('id_lugar', $idLugar)
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha', 'desc')
                ->limit(30)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'id_lugar' => $idLugar,
                    'total_visitas' => $cantidadVisitas->total_visitas,
                    'usuarios_unicos' => $cantidadVisitas->usuarios_unicos,
                    'visitas_anonimas' => $cantidadVisitas->visitas_anonimas,
                    'visitas_por_dia' => $visitasPorDia
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener cantidad de visitas por lugar', [
                'error' => $e->getMessage(),
                'id_lugar' => $idLugar
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la cantidad de visitas'
            ], 500);
        }
    }

    /**
     * Obtener tiempo promedio de visitas para un anunciante (todos sus lugares)
     */
    public function obtenerTiempoPromedioAnunciante($idUsuario)
    {
        try {
            // Validar si el usuario existe y es anunciante
            $usuario = DB::table('Usuario as u')
                ->join('Rol as r', 'u.id_rol', '=', 'r.id_rol')
                ->select('u.*', 'r.nombre as rol')
                ->where('u.id_usuario', $idUsuario)
                ->first();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if (strtolower($usuario->rol) !== 'anunciante') {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no es anunciante'
                ], 403);
            }

            // Obtener lugares del anunciante
            $lugares = DB::table('Lugar')
                ->where('id_usuario', $idUsuario)
                ->pluck('id_lugar');

            if ($lugares->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El anunciante no tiene lugares registrados'
                ], 404);
            }

            $tiempoPromedio = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio_global'),
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('SUM(tiempo_visita) as tiempo_total_acumulado')
                )
                ->whereIn('id_lugar', $lugares)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'id_usuario' => $idUsuario,
                    'nombre_usuario' => $usuario->nombre ?? 'Usuario',
                    'tiempo_promedio_global' => $tiempoPromedio->tiempo_promedio_global,
                    'total_visitas' => $tiempoPromedio->total_visitas,
                    'tiempo_total_acumulado' => $tiempoPromedio->tiempo_total_acumulado,
                    'total_lugares' => count($lugares)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tiempo promedio del anunciante', [
                'error' => $e->getMessage(),
                'id_usuario' => $idUsuario
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el tiempo promedio del anunciante'
            ], 500);
        }
    }

    /**
     * Obtener cantidad de visitas para un anunciante (todos sus lugares)
     */
    public function obtenerCantidadVisitasAnunciante($idUsuario)
    {
        try {
            // Validar si el usuario existe y es anunciante
            $usuario = DB::table('Usuario as u')
                ->join('Rol as r', 'u.id_rol', '=', 'r.id_rol')
                ->select('u.*', 'r.nombre as rol')
                ->where('u.id_usuario', $idUsuario)
                ->first();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if (strtolower($usuario->rol) !== 'anunciante') {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario no es anunciante'
                ], 403);
            }

            // Obtener lugares del anunciante
            $lugares = DB::table('Lugar')
                ->where('id_usuario', $idUsuario)
                ->pluck('id_lugar');

            if ($lugares->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El anunciante no tiene lugares registrados'
                ], 404);
            }

            // Resumen general de visitas
            $resumenVisitas = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('COUNT(DISTINCT id_usuario) as usuarios_unicos'),
                    DB::raw('COUNT(CASE WHEN id_usuario IS NULL THEN 1 END) as visitas_anonimas')
                )
                ->whereIn('id_lugar', $lugares)
                ->first();

            // Visitas por lugar del anunciante
            $visitasPorLugar = DB::table('estadisticas_visitas as ev')
                ->join('Lugar as l', 'ev.id_lugar', '=', 'l.id_lugar')
                ->select(
                    'l.id_lugar',
                    'l.nombre',
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('COUNT(DISTINCT ev.id_usuario) as usuarios_unicos')
                )
                ->whereIn('l.id_lugar', $lugares)
                ->groupBy('l.id_lugar', 'l.nombre')
                ->orderByDesc('total_visitas')
                ->get();

            // Visitas por día (últimos 30 días)
            $visitasPorDia = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('DATE(fecha) as fecha'),
                    DB::raw('COUNT(*) as visitas')
                )
                ->whereIn('id_lugar', $lugares)
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha', 'desc')
                ->limit(30)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'id_usuario' => $idUsuario,
                    'nombre_usuario' => $usuario->nombre ?? 'Usuario',
                    'resumen_visitas' => $resumenVisitas,
                    'visitas_por_lugar' => $visitasPorLugar,
                    'visitas_por_dia' => $visitasPorDia,
                    'total_lugares' => count($lugares)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener cantidad de visitas del anunciante', [
                'error' => $e->getMessage(),
                'id_usuario' => $idUsuario
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las visitas del anunciante'
            ], 500);
        }
    }

    /**
     * Método original mantenido para compatibilidad
     */
    public function obtenerEstadisticasPorLugar($idLugar)
    {
        try {
            $resumen = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio'),
                    DB::raw('SUM(tiempo_visita) as tiempo_total'),
                    DB::raw('COUNT(DISTINCT id_usuario) as usuarios_unicos')
                )
                ->where('id_lugar', $idLugar)
                ->first();

            $visitasPorDia = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('DATE(fecha) as fecha'),
                    DB::raw('COUNT(*) as visitas'),
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio')
                )
                ->where('id_lugar', $idLugar)
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha', 'desc')
                ->limit(30)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'id_lugar' => $idLugar,
                    'resumen' => $resumen,
                    'visitas_por_dia' => $visitasPorDia
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas por lugar', [
                'error' => $e->getMessage(),
                'id_lugar' => $idLugar
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del lugar'
            ], 500);
        }
    }

    /**
     * Método original del anunciante mantenido para compatibilidad
     */
    public function obtenerEstadisticasAnunciante($idUsuario)
    {
        try {
            Log::info('Iniciando obtenerEstadisticasAnunciante', ['id_usuario' => $idUsuario]);
            
            $usuario = DB::table('Usuario as u')
                ->join('Rol as r', 'u.id_rol', '=', 'r.id_rol')
                ->select('u.*', 'r.nombre as rol')
                ->where('u.id_usuario', $idUsuario)
                ->first();
            
            Log::info('Usuario encontrado', ['usuario' => $usuario]);

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if (strtolower($usuario->rol) !== 'anunciante') {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario con ID ' . $idUsuario . ' tiene el rol "' . $usuario->rol . '" y no es anunciante'
                ], 403);
            }

            $lugares = DB::table('Lugar')
                ->where('id_usuario', $idUsuario)
                ->pluck('id_lugar');

            Log::info('Lugares del anunciante', ['lugares' => $lugares->toArray()]);

            if ($lugares->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El anunciante no tiene lugares registrados'
                ], 404);
            }

            Log::info('Iniciando consulta de resumen');
            $resumen = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio'),
                    DB::raw('SUM(tiempo_visita) as tiempo_total'),
                    DB::raw('COUNT(DISTINCT id_usuario) as usuarios_unicos')
                )
                ->whereIn('id_lugar', $lugares)
                ->first();
                
            Log::info('Resumen obtenido', ['resumen' => $resumen]);

            $visitasPorDia = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('DATE(fecha) as fecha'),
                    DB::raw('COUNT(*) as visitas'),
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio')
                )
                ->whereIn('id_lugar', $lugares)
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha', 'desc')
                ->limit(30)
                ->get();

            $visitasPorLugar = DB::table('estadisticas_visitas as ev')
                ->join('Lugar as l', 'ev.id_lugar', '=', 'l.id_lugar')
                ->select(
                    'l.id_lugar',
                    'l.nombre',
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('ROUND(AVG(ev.tiempo_visita), 2) as tiempo_promedio'),
                    DB::raw('SUM(ev.tiempo_visita) as tiempo_total')
                )
                ->whereIn('l.id_lugar', $lugares)
                ->groupBy('l.id_lugar', 'l.nombre')
                ->orderByDesc('total_visitas')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'id_usuario' => $idUsuario,
                    'nombre_usuario' => $usuario->nombre ?? 'Usuario',
                    'resumen' => $resumen,
                    'visitas_por_dia' => $visitasPorDia,
                    'visitas_por_lugar' => $visitasPorLugar,
                    'total_lugares' => count($lugares)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas del anunciante', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'id_usuario' => $idUsuario
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del anunciante',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}