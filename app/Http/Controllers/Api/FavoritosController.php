<?php

namespace App\Http\Controllers\Api;

use App\Models\Favoritos;
use App\Models\Lugar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;

class FavoritosController extends Controller
{
    /**
     * Obtener el ID del usuario autenticado de manera segura
     */
    private function getUserId()
    {
        $usuario = Auth::user();
        return $usuario ? $usuario->id_usuario : null;
    }

    /**
     * Mostrar todos los favoritos del usuario autenticado
     */
    public function index()
    {
        try {
            $userId = $this->getUserId();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $favoritos = Favoritos::with(['lugar', 'usuario'])
                ->where('id_usuario', $userId)
                ->orderBy('fecha_agregado', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $favoritos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los favoritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar un lugar a favoritos
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_lugar' => 'required|integer|exists:Lugar,id_lugar'
        ]);

        try {
            $userId = $this->getUserId();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Verificar si ya existe en favoritos
            $existeFavorito = Favoritos::where('id_usuario', $userId)
                ->where('id_lugar', $request->id_lugar)
                ->first();

            if ($existeFavorito) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este lugar ya está en tus favoritos'
                ], 409);
            }

            $favorito = Favoritos::create([
                'id_usuario' => $userId,
                'id_lugar' => $request->id_lugar
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lugar agregado a favoritos exitosamente',
                'data' => $favorito->load(['lugar', 'usuario'])
            ], 201);

        } catch (QueryException $e) {
            // Manejo específico para violación de constraint UNIQUE
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este lugar ya está en tus favoritos'
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al agregar a favoritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un lugar de favoritos
     */
    public function destroy($id_lugar)
    {
        try {
            $userId = $this->getUserId();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $favorito = Favoritos::where('id_usuario', $userId)
                ->where('id_lugar', $id_lugar)
                ->first();

            if (!$favorito) {
                return response()->json([
                    'success' => false,
                    'message' => 'Favorito no encontrado'
                ], 404);
            }

            $favorito->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lugar eliminado de favoritos exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar de favoritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si un lugar está en favoritos
     */
    public function check($id_lugar)
    {
        try {
            $userId = $this->getUserId();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $esFavorito = Favoritos::where('id_usuario', $userId)
                ->where('id_lugar', $id_lugar)
                ->exists();

            return response()->json([
                'success' => true,
                'es_favorito' => $esFavorito
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar favorito',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle favorito (agregar si no existe, eliminar si existe)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'id_lugar' => 'required|integer|exists:Lugar,id_lugar'
        ]);

        try {
            $userId = $this->getUserId();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $favorito = Favoritos::where('id_usuario', $userId)
                ->where('id_lugar', $request->id_lugar)
                ->first();

            if ($favorito) {
                // Si existe, lo eliminamos
                $favorito->delete();
                return response()->json([
                    'success' => true,
                    'action' => 'removed',
                    'message' => 'Lugar eliminado de favoritos'
                ], 200);
            } else {
                // Si no existe, lo agregamos
                $nuevoFavorito = Favoritos::create([
                    'id_usuario' => $userId,
                    'id_lugar' => $request->id_lugar
                ]);

                return response()->json([
                    'success' => true,
                    'action' => 'added',
                    'message' => 'Lugar agregado a favoritos',
                    'data' => $nuevoFavorito->load(['lugar', 'usuario'])
                ], 201);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar favorito',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de favoritos del usuario
     */
    public function stats()
    {
        try {
            $userId = $this->getUserId();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $totalFavoritos = Favoritos::where('id_usuario', $userId)->count();
            
            $favoritosPorMes = Favoritos::where('id_usuario', $userId)
                ->selectRaw('MONTH(fecha_agregado) as mes, COUNT(*) as total')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_favoritos' => $totalFavoritos,
                    'favoritos_por_mes' => $favoritosPorMes
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}