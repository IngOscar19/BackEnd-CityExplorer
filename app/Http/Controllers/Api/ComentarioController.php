<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComentarioController extends Controller
{
    /**
     * Listar comentarios con filtros opcionales.
     */
    public function index(Request $request)
    {
        $rows = (int)$request->input('rows', 10);
        $page = 1 + (int)$request->input('page', 0);
        $idLugar = $request->input('id_lugar');
        $idUsuario = $request->input('id_usuario');

        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $query = Comentario::with(['usuario', 'lugar']);

        // Filtros opcionales
        if ($idLugar) {
            $query->porLugar($idLugar);
        }

        if ($idUsuario) {
            $query->porUsuario($idUsuario);
        }

        $comentarios = $query->orderBy('fecha_creacion', 'desc')->paginate($rows);

        return response()->json([
            'estatus' => 1,
            'data' => $comentarios->items(),
            'total' => $comentarios->total(),
        ]);
    }

    /**
     * Crear un nuevo comentario.
     */ 

 public function create(Request $request)
{
    $usuario = Auth::user();

    if (!$usuario) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'Usuario no autenticado',
        ], 401);
    }

    // Validación
    $validator = Validator::make($request->all(), [
        'contenido' => 'required|string|max:1000',
        'valoracion' => 'required|integer|between:1,5',
        'id_lugar' => 'required|exists:Lugar,id_lugar',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'Datos inválidos',
            'errores' => $validator->errors(),
        ], 422);
    }

    // Verificar si el usuario ya comentó este lugar
    $comentarioExistente = Comentario::where('id_usuario', $usuario->id_usuario)
                                     ->where('id_lugar', $request->id_lugar)
                                     ->first();

    if ($comentarioExistente) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'Ya has comentado este lugar. Puedes editar tu comentario existente.',
        ], 409);
    }

    try {
        $comentario = Comentario::create([
            'contenido' => $request->contenido,
            'valoracion' => $request->valoracion,
            'id_usuario' => $usuario->id_usuario,
            'id_lugar' => $request->id_lugar,
            'fecha_creacion' => now(), // Asegúrate de establecer fecha si no usas timestamps
        ]);

        $comentario->load(['usuario', 'lugar']);

        return response()->json([
            'estatus' => 1,
            'mensaje' => 'Comentario registrado con éxito',
            'data' => $comentario,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'Error al crear el comentario',
            'error' => $e->getMessage(),
        ], 500);
    }
}




    /**
     * Mostrar un comentario específico.
     */
    public function show($id)
    {
        $comentario = Comentario::with(['usuario', 'lugar'])->find($id);

        if (!$comentario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Comentario no encontrado',
            ], 404);
        }

        return response()->json([
            'estatus' => 1,
            'data' => $comentario,
        ]);
    }

    /**
     * Actualizar un comentario.
     */
    public function update(Request $request, $id)
    {
        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Comentario no encontrado',
            ], 404);
        }

        // Validación de datos
        $validator = Validator::make($request->all(), [
            'contenido' => 'nullable|string|max:1000',
            'valoracion' => 'nullable|integer|between:1,5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 422);
        }

        try {
            // Actualizar campos
            $comentario->update($request->only(['contenido', 'valoracion']));
            $comentario->load(['usuario', 'lugar']);

            return response()->json([
                'estatus' => 1,
                'mensaje' => 'Comentario actualizado con éxito',
                'data' => $comentario,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Error al actualizar el comentario',
            ], 500);
        }
    }

    /**
     * Eliminar un comentario.
     */
    public function destroy($id)
    {
        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Comentario no encontrado',
            ], 404);
        }

        try {
            $comentario->delete();

            return response()->json([
                'estatus' => 1,
                'mensaje' => 'Comentario eliminado con éxito',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Error al eliminar el comentario',
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de un lugar (promedio de valoración y total de comentarios).
     */
    public function estadisticasLugar($idLugar)
    {
        $promedioValoracion = Comentario::promedioValoracionPorLugar($idLugar);
        $totalComentarios = Comentario::totalComentariosPorLugar($idLugar);

        return response()->json([
            'estatus' => 1,
            'data' => [
                'promedio_valoracion' => round($promedioValoracion, 2),
                'total_comentarios' => $totalComentarios,
            ],
        ]);
    }

    /**
     * Obtener comentarios de un lugar específico.
     */
    public function comentariosPorLugar($idLugar, Request $request)
    {
        $rows = (int)$request->input('rows', 10);
        $page = 1 + (int)$request->input('page', 0);

        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $comentarios = Comentario::with(['usuario'])
                                ->porLugar($idLugar)
                                ->orderBy('fecha_creacion', 'desc')
                                ->paginate($rows);

        return response()->json([
            'estatus' => 1,
            'data' => $comentarios->items(),
            'total' => $comentarios->total(),
        ]);
    }
}