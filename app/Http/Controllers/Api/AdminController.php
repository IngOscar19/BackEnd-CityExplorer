<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
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

        $usuarios = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($usuarios);
    }

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
                'registros_recientes' => Usuario::where('created_at', '>=', now()->subDays(30))->count(),
                'registros_ultimo_mes' => Usuario::where('created_at', '>=', now()->subMonth())->count(),
            ];

            // Agregar estadísticas de lugares si existe la relación
            try {
                $estadisticas['usuarios_con_lugares'] = Usuario::whereHas('lugares')->count();
            } catch (\Exception $e) {
                // Si no existe la relación, no agregar esta estadística
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

        // Solo cargar lugares si la relación existe
        try {
            $usuarioDetalle->load(['lugares' => function($query) {
                $query->select('id_lugar', 'nombre', 'activo', 'id_usuario', 'created_at');
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
            'ultimo_login' => $usuarioDetalle->last_login ?? 'Nunca',
            'fecha_registro' => $usuarioDetalle->created_at,
            'estado_activo' => $usuarioDetalle->activo ? 'Activo' : 'Inactivo',
            'estado_bloqueo' => $usuarioDetalle->bloqueado ? 'Bloqueado' : 'No bloqueado'
        ];

        return response()->json($usuarioDetalle);
    }

    /**
     * Actualizar información básica de un usuario
     */
    public function update(Request $request, $id)
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        $usuarioAActualizar = Usuario::find($id);

        if (!$usuarioAActualizar) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        // Evitar que se modifique a sí mismo
        if ($usuarioAActualizar->id_usuario === $usuario->id_usuario) {
            return response()->json(['mensaje' => 'No puedes modificar tu propia cuenta desde esta función.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:255',
            'apellidoP' => 'sometimes|string|max:255',
            'apellidoM' => 'sometimes|string|max:255|nullable',
            'correo' => 'sometimes|email|unique:Usuario,correo,' . $id . ',id_usuario',
            'id_rol' => 'sometimes|exists:Rol,id_rol',
            'activo' => 'sometimes|boolean',
            'bloqueado' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // Solo actualizar campos permitidos
            $camposPermitidos = ['nombre', 'apellidoP', 'apellidoM', 'correo', 'id_rol', 'activo', 'bloqueado'];
            $datosActualizar = $request->only($camposPermitidos);

            $usuarioAActualizar->update($datosActualizar);

            DB::commit();

            return response()->json([
                'mensaje' => 'Usuario actualizado correctamente.',
                'usuario' => $usuarioAActualizar->fresh()->load('rol')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar usuario: ' . $e->getMessage()], 500);
        }
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
     * Cambiar rol de un usuario
     */
    public function cambiarRol(Request $request, $id)
    {
        $usuario = Auth::user()->load('rol');

        if (!$this->esAdministrador($usuario)) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }

        $usuarioACambiar = Usuario::with('rol')->find($id);

        if (!$usuarioACambiar) {
            return response()->json(['mensaje' => 'Usuario no encontrado.'], 404);
        }

        // Evitar cambiar su propio rol
        if ($usuarioACambiar->id_usuario === $usuario->id_usuario) {
            return response()->json(['mensaje' => 'No puedes cambiar tu propio rol.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'id_rol' => 'required|exists:Rol,id_rol'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $usuarioACambiar->update(['id_rol' => $request->id_rol]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Rol actualizado correctamente.',
                'usuario' => $usuarioACambiar->fresh()->load('rol')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al cambiar rol: ' . $e->getMessage()], 500);
        }
    }
}