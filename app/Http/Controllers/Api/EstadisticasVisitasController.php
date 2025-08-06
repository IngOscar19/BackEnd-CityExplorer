<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EstadisticasVisitasController extends Controller
{
    /**
     * Registrar o actualizar estadística de visita
     * POST /api/estadisticas-visitas
     */
    public function registrarVisita(Request $request)
    {
        try {
$validator = Validator::make($request->all(), [
    'id_lugar' => 'required|integer|exists:Lugar,id_lugar',
    'id_usuario' => 'nullable|integer|exists:Usuario,id_usuario',
    'tiempo_visita' => 'required|integer|min:1|max:86400'
]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $idLugar = $request->id_lugar;
            $idUsuario = $request->id_usuario;
            $tiempoVisita = $request->tiempo_visita;
            $fechaAhora = now();
            $fechaDia = $fechaAhora->toDateString();

            $visitaExistente = DB::table('estadisticas_visitas')
                ->where('id_lugar', $idLugar)
                ->where('id_usuario', $idUsuario)
                ->where('fecha', '>=', now()->subMinutes(5))
                ->orderBy('fecha', 'desc')
                ->first();

            if ($visitaExistente) {
                DB::table('estadisticas_visitas')
                    ->where('id', $visitaExistente->id)
                    ->update([
                        'tiempo_visita' => $tiempoVisita,
                        'fecha' => $fechaAhora,
                        'fecha_dia' => $fechaDia
                    ]);

                Log::info('Estadística de visita actualizada', [
                    'id_estadistica' => $visitaExistente->id,
                    'id_lugar' => $idLugar,
                    'id_usuario' => $idUsuario,
                    'tiempo_anterior' => $visitaExistente->tiempo_visita,
                    'tiempo_nuevo' => $tiempoVisita
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Estadística de visita actualizada correctamente',
                    'action' => 'updated',
                    'data' => [
                        'id' => $visitaExistente->id,
                        'tiempo_visita' => $tiempoVisita
                    ]
                ]);
            } else {
                $idEstadistica = DB::table('estadisticas_visitas')->insertGetId([
                    'id_lugar' => $idLugar,
                    'id_usuario' => $idUsuario,
                    'tiempo_visita' => $tiempoVisita,
                    'fecha' => $fechaAhora,
                    'fecha_dia' => $fechaDia
                ]);

                Log::info('Nueva estadística de visita creada', [
                    'id_estadistica' => $idEstadistica,
                    'id_lugar' => $idLugar,
                    'id_usuario' => $idUsuario,
                    'tiempo_visita' => $tiempoVisita
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Estadística de visita registrada correctamente',
                    'action' => 'created',
                    'data' => [
                        'id' => $idEstadistica,
                        'tiempo_visita' => $tiempoVisita
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error al registrar estadística de visita', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de un lugar específico
     */
    public function obtenerEstadisticasLugar($idLugar)
    {
        try {
            $lugar = DB::table('lugar')->where('id_lugar', $idLugar)->first();
            if (!$lugar) {
                return response()->json(['success' => false, 'message' => 'Lugar no encontrado'], 404);
            }

            $estadisticas = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio'),
                    DB::raw('MAX(tiempo_visita) as tiempo_maximo'),
                    DB::raw('MIN(tiempo_visita) as tiempo_minimo'),
                    DB::raw('SUM(tiempo_visita) as tiempo_total')
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
                ->where('fecha', '>=', now()->subDays(30))
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha', 'desc')
                ->get();

            $visitasPorHora = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('HOUR(fecha) as hora'),
                    DB::raw('COUNT(*) as visitas'),
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio')
                )
                ->where('id_lugar', $idLugar)
                ->groupBy(DB::raw('HOUR(fecha)'))
                ->orderBy('hora')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'lugar' => [
                        'id_lugar' => $lugar->id_lugar,
                        'nombre' => $lugar->nombre
                    ],
                    'resumen' => $estadisticas,
                    'visitas_por_dia' => $visitasPorDia,
                    'visitas_por_hora' => $visitasPorHora
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas del lugar', [
                'error' => $e->getMessage(),
                'id_lugar' => $idLugar
            ]);
            return response()->json(['success' => false, 'message' => 'Error al obtener estadísticas'], 500);
        }
    }

    /**
     * Obtener estadísticas de usuario
     */
    public function obtenerEstadisticasUsuario($idUsuario)
    {
        try {
            $usuario = DB::table('usuario')->where('id_usuario', $idUsuario)->first();
            if (!$usuario) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            $historialVisitas = DB::table('estadisticas_visitas as ev')
                ->join('lugar as l', 'ev.id_lugar', '=', 'l.id_lugar')
                ->select('l.nombre as nombre_lugar', 'l.id_lugar', 'ev.tiempo_visita', 'ev.fecha')
                ->where('ev.id_usuario', $idUsuario)
                ->orderBy('ev.fecha', 'desc')
                ->limit(50)
                ->get();

            $resumenUsuario = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio'),
                    DB::raw('SUM(tiempo_visita) as tiempo_total'),
                    DB::raw('COUNT(DISTINCT id_lugar) as lugares_visitados')
                )
                ->where('id_usuario', $idUsuario)
                ->first();

            $lugaresFavoritos = DB::table('estadisticas_visitas as ev')
                ->join('lugar as l', 'ev.id_lugar', '=', 'l.id_lugar')
                ->select('l.id_lugar', 'l.nombre',
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('ROUND(AVG(ev.tiempo_visita), 2) as tiempo_promedio'))
                ->where('ev.id_usuario', $idUsuario)
                ->groupBy('l.id_lugar', 'l.nombre')
                ->orderBy('total_visitas', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'usuario' => [
                        'id_usuario' => $usuario->id_usuario,
                        'name' => $usuario->nombre
                    ],
                    'resumen' => $resumenUsuario,
                    'historial_visitas' => $historialVisitas,
                    'lugares_favoritos' => $lugaresFavoritos
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas del usuario', [
                'error' => $e->getMessage(),
                'id_usuario' => $idUsuario
            ]);
            return response()->json(['success' => false, 'message' => 'Error al obtener estadísticas'], 500);
        }
    }

    /**
     * Obtener lugares más visitados
     */
    public function obtenerLugaresMasVisitados(Request $request)
    {
        try {
            $limite = $request->get('limite', 10);
            $fechaDesde = $request->get('fecha_desde');
            $fechaHasta = $request->get('fecha_hasta');

            $query = DB::table('estadisticas_visitas as ev')
                ->join('lugar as l', 'ev.id_lugar', '=', 'l.id_lugar')
                ->select(
                    'l.id_lugar', 'l.nombre', 'l.descripcion',
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('ROUND(AVG(ev.tiempo_visita), 2) as tiempo_promedio'),
                    DB::raw('SUM(ev.tiempo_visita) as tiempo_total'),
                    DB::raw('COUNT(DISTINCT ev.id_usuario) as usuarios_unicos')
                )
                ->groupBy('l.id_lugar', 'l.nombre', 'l.descripcion');

            if ($fechaDesde) $query->where('ev.fecha', '>=', $fechaDesde);
            if ($fechaHasta) $query->where('ev.fecha', '<=', $fechaHasta);

            $lugares = $query->orderBy('total_visitas', 'desc')->limit($limite)->get();

            return response()->json([
                'success' => true,
                'data' => $lugares,
                'filtros' => compact('limite', 'fechaDesde', 'fechaHasta')
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener lugares más visitados', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Error al obtener lugares más visitados'], 500);
        }
    }

    /**
     * Obtener resumen general del sistema
     */
    public function obtenerResumenGeneral(Request $request)
    {
        try {
            $fechaDesde = $request->get('fecha_desde', now()->subDays(30));
            $fechaHasta = $request->get('fecha_hasta', now());

            $estadisticasGenerales = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('COUNT(*) as total_visitas'),
                    DB::raw('COUNT(DISTINCT id_lugar) as lugares_visitados'),
                    DB::raw('COUNT(DISTINCT id_usuario) as usuarios_activos'),
                    DB::raw('ROUND(AVG(tiempo_visita), 2) as tiempo_promedio_global')
                )
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
                ->first();

            $visitasPorDia = DB::table('estadisticas_visitas')
                ->select(
                    DB::raw('DATE(fecha) as fecha'),
                    DB::raw('COUNT(*) as visitas'),
                    DB::raw('COUNT(DISTINCT id_usuario) as usuarios_unicos')
                )
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta])
                ->groupBy(DB::raw('DATE(fecha)'))
                ->orderBy('fecha', 'desc')
                ->get();

            $topLugares = DB::table('estadisticas_visitas as ev')
                ->join('lugar as l', 'ev.id_lugar', '=', 'l.id_lugar')
                ->select('l.nombre', DB::raw('COUNT(*) as visitas'))
                ->whereBetween('ev.fecha', [$fechaDesde, $fechaHasta])
                ->groupBy('l.id_lugar', 'l.nombre')
                ->orderBy('visitas', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => compact('fechaDesde', 'fechaHasta'),
                    'resumen_general' => $estadisticasGenerales,
                    'visitas_por_dia' => $visitasPorDia,
                    'top_lugares' => $topLugares
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener resumen general', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al obtener resumen general'], 500);
        }
    }

    /**
     * Eliminar estadísticas antiguas
     */
    public function limpiarEstadisticasAntiguas(Request $request)
    {
        try {
            $diasAntiguedad = $request->get('dias', 365);
            $fechaLimite = now()->subDays($diasAntiguedad);

            $registrosEliminados = DB::table('estadisticas_visitas')
                ->where('fecha', '<', $fechaLimite)
                ->delete();

            Log::info('Limpieza de estadísticas antiguas', compact('registrosEliminados', 'fechaLimite', 'diasAntiguedad'));

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$registrosEliminados} registros antiguos",
                'registros_eliminados' => $registrosEliminados
            ]);

        } catch (\Exception $e) {
            Log::error('Error al limpiar estadísticas antiguas', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al limpiar estadísticas antiguas'], 500);
        }
    }
     /**
     * Formatear tiempo en formato legible
     */
    private function formatearTiempo($segundos)
    {
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;

        if ($horas > 0) {
            return sprintf('%dh %dm %ds', $horas, $minutos, $segs);
        } elseif ($minutos > 0) {
            return sprintf('%dm %ds', $minutos, $segs);
        } else {
            return sprintf('%ds', $segs);
        }
    }
}
