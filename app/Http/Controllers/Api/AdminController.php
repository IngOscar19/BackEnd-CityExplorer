<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Lugar;
use App\Models\CategoriaLugar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Listar todos los usuarios (solo administradores)
     */
    public function index(Request $request)
    {
        $usuario = Auth::user()->load('rol');

        // Verificar que sea administrador
        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado. Solo los administradores pueden acceder a esta función.'], 403);
        }

        $query = Usuario::with('rol');

        // Filtros opcionales
        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->has('bloqueado')) {
            $query->where('bloqueado', $request->boolean('bloqueado'));
        }

        if ($request->has('rol')) {
            $query->whereHas('rol', function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->rol . '%');
            });
        }

        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', '%' . $buscar . '%')
                  ->orWhere('correo', 'like', '%' . $buscar . '%')
                  ->orWhere('apellidoP', 'like', '%' . $buscar . '%')
                  ->orWhere('apellidoM', 'like', '%' . $buscar . '%');
            });
        }

        $usuarios = $query->orderBy('id_usuario', 'desc')->paginate(15);

        return response()->json($usuarios);
    }

    /**
     * Listar todos los lugares (solo administradores)
     */
    public function lugares(Request $request)
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado. Solo los administradores pueden acceder a esta función.'], 403);
        }

        $query = Lugar::with(['usuario', 'categoria', 'direccion']);

        // Filtros opcionales
        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->has('categoria')) {
            $query->whereHas('categoria', function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->categoria . '%');
            });
        }

        if ($request->has('usuario')) {
            $query->whereHas('usuario', function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->usuario . '%')
                  ->orWhere('correo', 'like', '%' . $request->usuario . '%');
            });
        }

        if ($request->has('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', '%' . $buscar . '%')
                  ->orWhere('descripcion', 'like', '%' . $buscar . '%')
                  ->orWhere('num_telefonico', 'like', '%' . $buscar . '%')
                  ->orWhereHas('usuario', function($subq) use ($buscar) {
                      $subq->where('nombre', 'like', '%' . $buscar . '%')
                           ->orWhere('correo', 'like', '%' . $buscar . '%');
                  });
            });
        }

        // Filtro por días de servicio
        if ($request->has('dia_servicio')) {
            $query->whereJsonContains('dias_servicio', $request->dia_servicio);
        }

        // Filtro por horarios
        if ($request->has('horario_desde')) {
            $query->where('horario_apertura', '>=', $request->horario_desde);
        }

        if ($request->has('horario_hasta')) {
            $query->where('horario_cierre', '<=', $request->horario_hasta);
        }

        $lugares = $query->orderBy('id_lugar', 'desc')->paginate(20);

        return response()->json($lugares);
    }

    /**
     * Ver detalles de un lugar específico
     */
    public function mostrarLugar($id)
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        $lugar = Lugar::with(['usuario.rol', 'categoria', 'direccion', 'imagenes'])->find($id);

        if (!$lugar) {
            return response()->json(['mensaje' => 'Lugar no encontrado.'], 404);
        }

        // Agregar información adicional del lugar
        $lugar->informacion_adicional = [
            'estado_texto' => $this->obtenerEstadoLugar($lugar),
            'dias_servicio_texto' => is_array($lugar->dias_servicio) 
                ? implode(', ', $lugar->dias_servicio) 
                : $lugar->dias_servicio,
            'horario_completo' => $lugar->horario_apertura && $lugar->horario_cierre 
                ? $lugar->horario_apertura . ' - ' . $lugar->horario_cierre 
                : 'No definido',
            'total_imagenes' => $lugar->imagenes->count(),
            'propietario' => $lugar->usuario->nombre . ' ' . $lugar->usuario->apellidoP,
            'categoria_nombre' => $lugar->categoria->nombre ?? 'Sin categoría'
        ];

        return response()->json($lugar);
    }


    /**
     * Bloquear/Desbloquear un lugar (toggle del campo bloqueado)
     */
    public function toggleEstadoLugar($id, Request $request)
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        $lugar = Lugar::with(['usuario', 'categoria'])->find($id);

        if (!$lugar) {
            return response()->json(['mensaje' => 'Lugar no encontrado.'], 404);
        }

        // Verificar que el lugar no esté eliminado
        if (!$lugar->activo) {
            return response()->json(['mensaje' => 'No se puede cambiar el estado de un lugar eliminado.'], 422);
        }

        DB::beginTransaction();

        try {
            // Si NO está bloqueado (false), lo bloqueamos (true); si está bloqueado (true), lo desbloqueamos (false)
            if (!$lugar->bloqueado) {
                // Bloquear lugar
                $validator = Validator::make($request->all(), [
                    'motivo' => 'nullable|string|max:500'
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }

                $lugar->update([
                    'bloqueado' => true,
                    'fecha_bloqueo' => now(),
                    'motivo_bloqueo' => $request->motivo ?? 'Bloqueado por administrador',
                    'bloqueado_por' => $usuario->id_usuario,
                    'fecha_desbloqueo' => null,
                    'desbloqueado_por' => null
                ]);

                $mensaje = 'Lugar bloqueado correctamente.';
                $nuevoEstado = 'Bloqueado';
            } else {
                // Desbloquear lugar
                $lugar->update([
                    'bloqueado' => false,
                    'fecha_bloqueo' => null,
                    'motivo_bloqueo' => null,
                    'bloqueado_por' => null,
                    'fecha_desbloqueo' => now(),
                    'desbloqueado_por' => $usuario->id_usuario
                ]);

                $mensaje = 'Lugar desbloqueado correctamente.';
                $nuevoEstado = 'Disponible';
            }

            DB::commit();

            return response()->json([
                'mensaje' => $mensaje,
                'lugar' => $lugar->fresh()->load(['usuario', 'categoria']),
                'nuevo_estado' => $nuevoEstado,
                'bloqueado' => $lugar->bloqueado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al cambiar estado del lugar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener estadísticas de lugares
     */
    public function estadisticasLugares()
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        try {
            $estadisticas = [
                'total_lugares' => Lugar::count(),
                'lugares_activos' => Lugar::where('activo', true)->count(),
                'lugares_inactivos' => Lugar::where('activo', false)->count(),
                'lugares_por_categoria' => Lugar::select('Categoria.nombre as categoria', DB::raw('count(*) as total'))
                    ->join('Categoria', 'Lugar.id_categoria', '=', 'Categoria.id_categoria')
                    ->groupBy('Categoria.nombre', 'Categoria.id_categoria')
                    ->get(),
                'lugares_con_telefono' => Lugar::whereNotNull('num_telefonico')
                    ->where('num_telefonico', '!=', '')
                    ->count(),
                'lugares_con_web' => Lugar::whereNotNull('paginaWeb')
                    ->where('paginaWeb', '!=', '')
                    ->count(),
                'lugares_con_horario' => Lugar::whereNotNull('horario_apertura')
                    ->whereNotNull('horario_cierre')
                    ->count(),
                'lugares_con_imagenes' => Lugar::whereHas('imagenes')->count(),
                'promedio_imagenes_por_lugar' => round(
                    Lugar::withCount('imagenes')->get()->avg('imagenes_count'), 2
                )
            ];

            return response()->json($estadisticas);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar estadísticas: ' . $e->getMessage()], 500);
        }
    }

    

    /**
     * Obtener lugares por categoría
     */
    public function lugaresPorCategoria(Request $request)
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        $query = Lugar::with(['usuario', 'categoria', 'direccion']);

        if ($request->has('categoria_id')) {
            $query->where('id_categoria', $request->categoria_id);
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $lugares = $query->orderBy('nombre', 'asc')->paginate(15);

        // Agregar información de la categoría
        $categoria = null;
        if ($request->has('categoria_id')) {
            $categoria = CategoriaLugar::find($request->categoria_id); // Cambiado a CategoriaLugar
        }

        return response()->json([
            'lugares' => $lugares,
            'categoria' => $categoria
        ]);
    }

/**
     * Obtener lugares sin imágenes
     */
    public function lugaresSinImagenes(Request $request)
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        $query = Lugar::with(['usuario', 'categoria'])
                     ->doesntHave('imagenes');

        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $lugaresSinImagenes = $query->orderBy('id_lugar', 'desc')->paginate(15);

        return response()->json($lugaresSinImagenes);
    }


    // Métodos existentes del controlador original para usuarios...

    /**
     * Bloquear un usuario (cambiar bloqueado a true)
     */
    public function bloquearUsuario($id, Request $request)
    {
        $usuario = Auth::user()->load('rol');

        // Verificar que sea administrador
        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado. Solo los administradores pueden bloquear usuarios.'], 403);
        }

        $usuarioABloquear = Usuario::with('rol')->find($id);

        if (!$usuarioABloquear) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        // Evitar que el admin se bloquee a sí mismo
        if ($usuarioABloquear->id_usuario === $usuario->id_usuario) {
            return response()->json(['mensaje' => 'No puedes bloquearte a ti mismo.'], 422);
        }

        // Evitar bloquear a otros administradores
        if ($this->esAdministrador($usuarioABloquear)) {
            return response()->json(['mensaje' => 'No se puede bloquear a otro administrador.'], 422);
        }

        // Verificar si ya está bloqueado
        if ($usuarioABloquear->bloqueado) {
            return response()->json(['mensaje' => 'El usuario ya está bloqueado.'], 422);
        }

        DB::beginTransaction();

        try {
            // Validar motivo del bloqueo
            $validator = Validator::make($request->all(), [
                'motivo' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Actualizar estado del usuario
            $usuarioABloquear->update([
                'bloqueado' => true,
                'fecha_bloqueo' => now(),
                'motivo_bloqueo' => $request->motivo ?? 'Bloqueado por administrador',
                'bloqueado_por' => $usuario->id_usuario
            ]);

            // Revocar todos los tokens del usuario bloqueado
            $usuarioABloquear->tokens()->delete();

            DB::commit();

            return response()->json([
                'mensaje' => 'Usuario bloqueado correctamente.',
                'usuario' => $usuarioABloquear->fresh()->load('rol')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al bloquear usuario: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Desbloquear un usuario (cambiar bloqueado a false)
     */
    public function desbloquearUsuario($id)
    {
        $usuario = Auth::user()->load('rol');

        // Verificar que sea administrador
        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado. Solo los administradores pueden desbloquear usuarios.'], 403);
        }

        $usuarioADesbloquear = Usuario::with('rol')->find($id);

        if (!$usuarioADesbloquear) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        // Verificar si ya está desbloqueado
        if (!$usuarioADesbloquear->bloqueado) {
            return response()->json(['mensaje' => 'El usuario ya está desbloqueado.'], 422);
        }

        DB::beginTransaction();

        try {
            // Actualizar estado del usuario
            $usuarioADesbloquear->update([
                'bloqueado' => false,
                'fecha_bloqueo' => null,
                'motivo_bloqueo' => null,
                'bloqueado_por' => null,
                'fecha_desbloqueo' => now(),
                'desbloqueado_por' => $usuario->id_usuario
            ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Usuario desbloqueado correctamente.',
                'usuario' => $usuarioADesbloquear->fresh()->load('rol')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al desbloquear usuario: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Alternar estado de bloqueo de un usuario (bloquear/desbloquear)
     */
    public function toggleEstadoUsuario($id, Request $request)
    {
        $usuario = Auth::user()->load('rol');

        // Verificar que sea administrador
        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado. Solo los administradores pueden cambiar el estado de usuarios.'], 403);
        }

        $usuarioAModificar = Usuario::with('rol')->find($id);

        if (!$usuarioAModificar) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        // Evitar que el admin se modifique a sí mismo
        if ($usuarioAModificar->id_usuario === $usuario->id_usuario) {
            return response()->json(['mensaje' => 'No puedes cambiar tu propio estado.'], 422);
        }

        // Evitar cambiar estado a otros administradores
        if ($this->esAdministrador($usuarioAModificar)) {
            return response()->json(['mensaje' => 'No se puede cambiar el estado de otro administrador.'], 422);
        }

        DB::beginTransaction();

        try {
            // Si NO está bloqueado (false), lo bloqueamos (true); si está bloqueado (true), lo desbloqueamos (false)
            if (!$usuarioAModificar->bloqueado) {
                // Bloquear usuario
                $validator = Validator::make($request->all(), [
                    'motivo' => 'nullable|string|max:500'
                ]);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()], 422);
                }

                $usuarioAModificar->update([
                    'bloqueado' => true,
                    'fecha_bloqueo' => now(),
                    'motivo_bloqueo' => $request->motivo ?? 'Bloqueado por administrador',
                    'bloqueado_por' => $usuario->id_usuario,
                    'fecha_desbloqueo' => null,
                    'desbloqueado_por' => null
                ]);

                // Revocar tokens del usuario bloqueado
                $usuarioAModificar->tokens()->delete();

                $mensaje = 'Usuario bloqueado correctamente.';
                $nuevoEstado = 'Bloqueado';
            } else {
                // Desbloquear usuario
                $usuarioAModificar->update([
                    'bloqueado' => false,
                    'fecha_bloqueo' => null,
                    'motivo_bloqueo' => null,
                    'bloqueado_por' => null,
                    'fecha_desbloqueo' => now(),
                    'desbloqueado_por' => $usuario->id_usuario
                ]);

                $mensaje = 'Usuario desbloqueado correctamente.';
                $nuevoEstado = 'Activo';
            }

            DB::commit();

            return response()->json([
                'mensaje' => $mensaje,
                'usuario' => $usuarioAModificar->fresh()->load('rol'),
                'nuevo_estado' => $nuevoEstado,
                'bloqueado' => $usuarioAModificar->bloqueado
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al cambiar estado del usuario: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener estadísticas de usuarios (solo administradores)
     */
    public function estadisticas()
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        try {
            $estadisticas = [
                'total_usuarios' => Usuario::count(),
                'usuarios_activos' => Usuario::where('activo', true)->count(),
                'usuarios_inactivos' => Usuario::where('activo', false)->count(),
                'usuarios_bloqueados' => Usuario::where('bloqueado', true)->count(),
                'usuarios_no_bloqueados' => Usuario::where('bloqueado', false)->count(),
                'usuarios_por_rol' => Usuario::select('Rol.nombre as rol', DB::raw('count(*) as total'))
                    ->join('Rol', 'Usuario.id_rol', '=', 'Rol.id_rol')
                    ->groupBy('Rol.nombre', 'Rol.id_rol')
                    ->get(),
                'registros_recientes' => Usuario::whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->count(),
                'registros_ultimo_mes' => Usuario::whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)')->count(),
            ];

            // Agregar estadísticas de lugares
            try {
                $estadisticas['usuarios_con_lugares'] = Usuario::whereHas('lugares')->count();
                $estadisticas['promedio_lugares_por_usuario'] = round(
                    Usuario::withCount('lugares')->get()->avg('lugares_count'), 2
                );
            } catch (\Exception $e) {
                // Si no existe la relación, no agregar estas estadísticas
            }

            return response()->json($estadisticas);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar estadísticas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ver detalles de un usuario específico
     */
    public function show($id)
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        $usuarioDetalle = Usuario::with(['rol'])->find($id);

        if (!$usuarioDetalle) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        // Cargar lugares del usuario
        try {
            $usuarioDetalle->load(['lugares' => function($query) {
                $query->select('id_lugar', 'nombre', 'activo', 'id_usuario')
                      ->with('categoria:id_categoria,nombre');
            }]);
            
            $lugaresCount = $usuarioDetalle->lugares->count();
            $lugaresActivos = $usuarioDetalle->lugares->where('activo', true)->count();
        } catch (\Exception $e) {
            $lugaresCount = 0;
            $lugaresActivos = 0;
        }

        // Agregar información adicional
        $usuarioDetalle->estadisticas = [
            'lugares_creados' => $lugaresCount,
            'lugares_activos' => $lugaresActivos,
            'lugares_inactivos' => $lugaresCount - $lugaresActivos,
            'ultimo_login' => $usuarioDetalle->last_login ?? 'Nunca',
            'fecha_registro' => $usuarioDetalle->created_at,
            'estado_activo' => $usuarioDetalle->activo ? 'Activo' : 'Inactivo',
            'estado_bloqueo' => $usuarioDetalle->bloqueado ? 'Bloqueado' : 'No bloqueado'
        ];

        return response()->json($usuarioDetalle);
    }

    

    /**
     * Obtener historial de acciones de un usuario
     */
    public function historial($id)
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        $usuarioConsulta = Usuario::with('rol')->find($id);

        if (!$usuarioConsulta) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        // Información del historial basada en los campos existentes
        $historial = [
            'usuario' => [
                'id' => $usuarioConsulta->id_usuario,
                'nombre_completo' => $usuarioConsulta->nombre_completo,
                'correo' => $usuarioConsulta->correo,
                'rol' => $usuarioConsulta->rol->nombre ?? 'Sin rol'
            ],
            'fechas' => [
                'fecha_registro' => $usuarioConsulta->created_at,
                'ultima_actualizacion' => $usuarioConsulta->updated_at,
                'ultimo_login' => $usuarioConsulta->last_login ?? null,
            ],
            'estado_actual' => [
                'activo' => $usuarioConsulta->activo,
                'bloqueado' => $usuarioConsulta->bloqueado,
                'estado_texto' => $usuarioConsulta->bloqueado ? 'Bloqueado' : ($usuarioConsulta->activo ? 'Activo' : 'Inactivo')
            ],
            'bloqueo' => [
                'fecha_bloqueo' => $usuarioConsulta->fecha_bloqueo ?? null,
                'motivo_bloqueo' => $usuarioConsulta->motivo_bloqueo ?? null,
                'bloqueado_por' => $usuarioConsulta->bloqueado_por ?? null,
                'fecha_desbloqueo' => $usuarioConsulta->fecha_desbloqueo ?? null,
                'desbloqueado_por' => $usuarioConsulta->desbloqueado_por ?? null,
                'dias_desde_bloqueo' => $usuarioConsulta->dias_desde_bloqueo
            ]
        ];

        return response()->json($historial);
    }

    

    /**
     * Método auxiliar para verificar si un usuario es administrador
     */
    protected function esAdministrador($usuario)
    {
        return $usuario && 
               $usuario->rol && 
               (strcasecmp($usuario->rol->nombre, 'administrador') === 0 || 
                strcasecmp($usuario->rol->nombre, 'Administrador') === 0);
    }

    /**
     * Método auxiliar para obtener el estado legible de un lugar
     */
    protected function obtenerEstadoLugar($lugar)
    {
        if (!$lugar->activo) {
            return 'Inactivo';
        }
        
        return 'Activo';
    }




    /**
     * Obtener dashboard completo con estadísticas generales
     */
    public function dashboard()
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        try {
            $dashboard = [
                'usuarios' => [
                    'total' => Usuario::count(),
                    'activos' => Usuario::where('activo', true)->count(),
                    'bloqueados' => Usuario::where('bloqueado', true)->count(),
                    'nuevos_mes' => Usuario::whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)')->count()
                ],
                'lugares' => [
                    'total' => Lugar::count(),
                    'activos' => Lugar::where('activo', true)->count(),
                    'inactivos' => Lugar::where('activo', false)->count(),
                    'con_imagenes' => Lugar::whereHas('imagenes')->count()
                ],
                'categorias_populares' => Lugar::select('CategoriaLugar.nombre as categoria', DB::raw('count(*) as total'))
                    ->join('CategoriaLugar', 'Lugar.id_categoria', '=', 'CategoriaLugar.id_categoria')
                    ->groupBy('CategoriaLugar.nombre', 'CategoriaLugar.id_categoria')
                    ->orderBy('total', 'desc')
                    ->limit(5)
                    ->get(),
                'actividad_reciente' => [
                    'usuarios_nuevos' => Usuario::whereRaw('DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')->count(),
                ]
            ];

            return response()->json($dashboard);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar dashboard: ' . $e->getMessage()], 500);
        }
    }
}