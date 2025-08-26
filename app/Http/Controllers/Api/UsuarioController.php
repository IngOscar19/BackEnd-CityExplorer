<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    /**
     * Listar usuarios (con paginación personalizada).
     */
    public function index(Request $request)
    {
        $rows = (int)$request->input('rows', 10);
        $page = 1 + (int)$request->input('page', 0);

        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        // Query base con relaciones
        $query = Usuario::with('rol');

        // Filtros opcionales (similares al AdminController)
        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->has('rol')) {
            $query->whereHas('rol', function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->rol . '%');
            });
        }

        if ($request->has('buscar')) {
            $query->buscar($request->buscar); // Usando el scope del modelo
        }

        $usuarios = $query->orderBy('created_at', 'desc')->paginate($rows);

        return response()->json([
            'estatus' => 1,
            'data' => $usuarios->items(),
            'total' => $usuarios->total(),
            'current_page' => $usuarios->currentPage(),
            'last_page' => $usuarios->lastPage(),
        ]);
    }

    /**
    * Crear un nuevo usuario (registro público) - SIN confirmación de password
    */
    public function create(Request $request)
 {
    // Validación de datos mejorada - SIN password_confirmation
    $validator = Validator::make($request->all(), [
        'nombre' => 'required|string|max:50',
        'apellidoP' => 'required|string|max:50',
        'apellidoM' => 'nullable|string|max:50',
        'correo' => 'required|email|unique:Usuario,correo', 
        'password' => 'required|string|min:6', 
        'id_rol' => 'nullable|exists:Rol,id_rol',
        'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'Errores de validación',
            'errores' => $validator->errors(),
        ], 422);
    }

    try {
        // Crear el usuario
        $usuario = new Usuario();
        $usuario->nombre = $request->nombre;
        $usuario->apellidoP = $request->apellidoP;
        $usuario->apellidoM = $request->apellidoM;
        $usuario->correo = $request->correo;
        $usuario->password = Hash::make($request->password);
        $usuario->id_rol = $request->id_rol ?? 1; // Usuario normal por defecto
        $usuario->activo = 1; // Activo por defecto (usando 1 en lugar de true)

        // Manejo de imagen de perfil
        if ($request->hasFile('foto_perfil')) {
            $archivo = $request->file('foto_perfil');
            $nombreArchivo = time() . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
            $ruta = $archivo->storeAs('fotos_perfil', $nombreArchivo, 'public');
            $usuario->foto_perfil = $ruta;
        }

        $usuario->save();

        // Cargar relación para la respuesta
        $usuario->load('rol');

        // Crear token de autenticación
        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'estatus' => 1,
            'mensaje' => 'Usuario registrado con éxito',
            'data' => [
                'usuario' => $usuario,
                'token' => $token
            ],
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'Error al registrar usuario',
            'error' => $e->getMessage(),
        ], 500);
    }
  }

    /**
     * Mostrar un usuario específico.
     */
    public function show(string $id)
    {
        try {
            $usuario = Usuario::with(['rol', 'lugares'])->find($id);

            if (!$usuario) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'Usuario no encontrado',
                ], 404);
            }

            // Ocultar información sensible si no es el mismo usuario o admin
            $usuarioAuth = Auth::user();
            if (!$usuarioAuth || ($usuarioAuth->id_usuario !== $usuario->id_usuario && !$usuarioAuth->esAdministrador())) {
                $usuario->makeHidden(['correo', 'created_at', 'updated_at', 'last_login']);
            }

            return response()->json([
                'estatus' => 1,
                'data' => $usuario,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Error al obtener usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Actualizar un usuario - Mejorado con validaciones de seguridad
     */
    public function update(Request $request, $id)
    {
        try {
            $usuario = Usuario::find($id);
            
            if (!$usuario) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'Usuario no encontrado'
                ], 404);
            }

            // Verificar permisos: solo el mismo usuario o admin puede actualizar
            $usuarioAuth = Auth::user();
            if (!$usuarioAuth || ($usuarioAuth->id_usuario !== $usuario->id_usuario && !$usuarioAuth->esAdministrador())) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'No tienes permisos para actualizar este usuario'
                ], 403);
            }

            // Verificar si el usuario está bloqueado (no puede actualizar si está bloqueado)
            if (!$usuario->activo && !$usuarioAuth->esAdministrador()) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'No puedes actualizar tu perfil mientras estés bloqueado'
                ], 403);
            }

            // Obtener datos de la petición
            $input = [];
            
            if ($request->filled('nombre')) {
                $input['nombre'] = $request->input('nombre');
            }
            
            if ($request->filled('apellidoP')) {
                $input['apellidoP'] = $request->input('apellidoP');
            }
            
            if ($request->has('apellidoM')) {
                $input['apellidoM'] = $request->input('apellidoM');
            }
            
            if ($request->filled('correo')) {
                $input['correo'] = $request->input('correo');
            }
            
            if ($request->filled('password')) {
                $input['password'] = $request->input('password');
            }
            
            // Solo admin puede cambiar rol
            if ($request->filled('id_rol') && $usuarioAuth->esAdministrador()) {
                $input['id_rol'] = (int) $request->input('id_rol');
            }

            // Si no hay datos para actualizar
            if (empty($input) && !$request->hasFile('foto_perfil')) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'No se recibieron datos para actualizar'
                ], 400);
            }

            // Validación dinámica
            $rules = [];
            if (isset($input['nombre'])) $rules['nombre'] = 'required|string|max:50';
            if (isset($input['apellidoP'])) $rules['apellidoP'] = 'required|string|max:50';
            if (array_key_exists('apellidoM', $input)) $rules['apellidoM'] = 'nullable|string|max:50';
            if (isset($input['correo'])) $rules['correo'] = 'required|email|unique:Usuario,correo,'.$id.',id_usuario';
            if (isset($input['password'])) $rules['password'] = 'required|string|min:6';
            if (isset($input['id_rol'])) $rules['id_rol'] = 'required|exists:Rol,id_rol';

            if (!empty($rules)) {
                $validator = Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    return response()->json([
                        'estatus' => 0,
                        'mensaje' => 'Errores de validación',
                        'errores' => $validator->errors()
                    ], 422);
                }
            }

            // Actualizar campos
            foreach ($input as $campo => $valor) {
                if ($campo === 'password') {
                    $usuario->password = Hash::make($valor);
                } else {
                    $usuario->$campo = $valor;
                }
            }

            // Manejo de imagen mejorado
            if ($request->hasFile('foto_perfil')) {
                // Validar imagen
                $validator = Validator::make($request->all(), [
                    'foto_perfil' => 'image|mimes:jpg,jpeg,png,webp,gif|max:2048'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'estatus' => 0,
                        'mensaje' => 'Error en la imagen',
                        'errores' => $validator->errors()
                    ], 422);
                }

                // Eliminar imagen anterior
                if ($usuario->foto_perfil && Storage::disk('public')->exists($usuario->foto_perfil)) {
                    Storage::disk('public')->delete($usuario->foto_perfil);
                }

                // Guardar nueva imagen
                $archivo = $request->file('foto_perfil');
                $nombreArchivo = time() . '_' . uniqid() . '.' . $archivo->getClientOriginalExtension();
                $ruta = $archivo->storeAs('fotos_perfil', $nombreArchivo, 'public');
                $usuario->foto_perfil = $ruta;
            }

            $usuario->save();

            return response()->json([
                'estatus' => 1,
                'mensaje' => 'Usuario actualizado con éxito',
                'data' => $usuario->fresh()->load('rol')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Error al actualizar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   /**
 * Eliminar un usuario (soft delete - cambiar activo a false).
 */
public function destroy($id)
{
    try {
        $usuario = Usuario::with('rol')->find($id);

        if (!$usuario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Usuario no encontrado',
            ], 404);
        }

        // Verificar permisos
        $usuarioAuth = Auth::user()->load('rol');
        if (!$usuarioAuth || ($usuarioAuth->id_usuario !== $usuario->id_usuario && !$usuarioAuth->esAdministrador())) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'No tienes permisos para eliminar este usuario'
            ], 403);
        }

        // Evitar que admin se elimine a sí mismo
        if ($usuarioAuth->id_usuario === $usuario->id_usuario && $usuarioAuth->esAdministrador()) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Un administrador no puede eliminarse a sí mismo'
            ], 422);
        }

        // Evitar eliminar a otros administradores (solo admins pueden eliminar usuarios)
        if ($usuario->esAdministrador() && $usuarioAuth->id_usuario !== $usuario->id_usuario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'No se puede eliminar a otro administrador'
            ], 422);
        }

        // Verificar si ya está inactivo
        if (!$usuario->activo) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'El usuario ya está inactivo'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Soft delete: cambiar activo a false en lugar de eliminar físicamente
            $usuario->update([
                'activo' => false,
            ]);

            // Revocar todos los tokens del usuario
            $usuario->tokens()->delete();

            DB::commit();

            return response()->json([
                'estatus' => 1,
                'mensaje' => 'Usuario desactivado correctamente',
                'usuario' => [
                    'id' => $usuario->id_usuario,
                    'nombre_completo' => $usuario->nombre_completo,
                    'correo' => $usuario->correo,
                    'activo' => $usuario->activo,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    } catch (\Exception $e) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'Error al desactivar usuario',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
    * Restaurar un usuario eliminado (cambiar activo a true).
    */
    public function restore($id) 
 {
    try {
        $usuarioAuth = Auth::user()->load('rol');

        // Solo administradores pueden restaurar usuarios
        if (!$usuarioAuth->esAdministrador()) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'No autorizado. Solo los administradores pueden restaurar usuarios.'
            ], 403);
        }

        $usuario = Usuario::with('rol')->find($id);

        if (!$usuario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Usuario no encontrado',
            ], 404);
        }

        // Verificar si ya está activo
        if ($usuario->activo) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'El usuario ya está activo'
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Restaurar usuario: cambiar activo a true
            $usuario->update([
                'activo' => true,
            ]);

            DB::commit();

            return response()->json([
                'estatus' => 1,
                'mensaje' => 'Usuario restaurado correctamente',
                'usuario' => [
                    'id' => $usuario->id_usuario,
                    'nombre_completo' => $usuario->nombre_completo,
                    'correo' => $usuario->correo,
                    'activo' => $usuario->activo,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    } catch (\Exception $e) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'Error al restaurar usuario',
            'error' => $e->getMessage()
        ], 500);
    }
 }

    /**
     * Iniciar sesión mejorado.
     */
    public function login(Request $request)
    {
        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'correo' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'Datos de login inválidos',
                    'errores' => $validator->errors(),
                ], 422);
            }

            // Buscar el usuario por correo
            $usuario = Usuario::with('rol')->where('correo', $request->correo)->first();

            if (!$usuario || !Hash::check($request->password, $usuario->password)) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'Credenciales incorrectas',
                ], 401);
            }

            // Verificar si el usuario está bloqueado
            if (!$usuario->activo) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'Tu cuenta está bloqueada',
                    'motivo' => $usuario->motivo_bloqueo,
                    'fecha_bloqueo' => $usuario->fecha_bloqueo,
                ], 403);
            }

            // Actualizar último login
            $usuario->actualizarUltimoLogin();

            // Crear token
            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'estatus' => 1,
                'mensaje' => 'Inicio de sesión exitoso',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'data' => $usuario,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Error en el login',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cerrar sesión.
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'estatus' => 1,
                'mensaje' => 'Sesión cerrada exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Error al cerrar sesión',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener perfil del usuario autenticado.
     */
    public function perfil(Request $request)
    {
        try {
            $usuario = $request->user()->load('rol');

            return response()->json([
                'estatus' => 1,
                'data' => $usuario,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Error al obtener perfil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cambiar contraseña del usuario autenticado.
     */
    public function cambiarPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password_actual' => 'required|string',
                'password_nuevo' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'Datos inválidos',
                    'errores' => $validator->errors(),
                ], 422);
            }

            $usuario = $request->user();

            // Verificar contraseña actual
            if (!Hash::check($request->password_actual, $usuario->password)) {
                return response()->json([
                    'estatus' => 0,
                    'mensaje' => 'La contraseña actual es incorrecta',
                ], 422);
            }

            // Actualizar contraseña
            $usuario->password = Hash::make($request->password_nuevo);
            $usuario->save();

            // Revocar otros tokens por seguridad
            $usuario->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

            return response()->json([
                'estatus' => 1,
                'mensaje' => 'Contraseña actualizada exitosamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Error al cambiar contraseña',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}