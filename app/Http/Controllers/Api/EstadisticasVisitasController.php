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
                'id_lugar' => 'required|integer|exists:lugar,id_lugar',
                'tiempo_visita' => 'required|integer|min:1',
                'id_usuario' => 'nullable|integer|exists:usuario,id_usuario'
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
     * Obtener estadísticas por ID de lugar
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
     * Obtener estadísticas globales por anunciante
     */
    public function obtenerEstadisticasAnunciante($idAnunciante)
    {
        try {
            // Validar si es un anunciante
            $usuario = DB::table('usuario')->where('id_usuario', $idAnunciante)->first();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            if ($usuario->rol !== 'anunciante') {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario con ID ' . $idAnunciante . ' tiene el rol "' . $usuario->rol . '" y no es anunciante'
                ], 403);
            }

            // Obtener todos los lugares del anunciante
            $lugares = DB::table('lugar')
                ->where('id_usuario', $idAnunciante)
                ->pluck('id_lugar');

            if ($lugares->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El anunciante no tiene lugares registrados'
                ], 404);
            }

            // Resumen general
            $resumen = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio'),
                    DB::raw('SUM(tiempo_visita) as tiempo_total'),
                    DB::raw('COUNT(DISTINCT id_usuario) as usuarios_unicos')
                )
                ->whereIn('id_lugar', $lugares)
                ->first();

            // Por día
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

            // Por lugar
            $visitasPorLugar = DB::table('estadisticas_visitas as ev')
                ->join('lugar as l', 'ev.id_lugar', '=', 'l.id_lugar')
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
                    'id_anunciante' => $idAnunciante,
                    'resumen' => $resumen,
                    'visitas_por_dia' => $visitasPorDia,
                    'visitas_por_lugar' => $visitasPorLugar
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas del anunciante', [
                'error' => $e->getMessage(),
                'id_anunciante' => $idAnunciante
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del anunciante'
            ], 500);
        }
    }
}
