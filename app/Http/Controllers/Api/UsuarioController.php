<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    /**
     * Listar usuarios.
     */
    public function index(Request $request)
    {
        $rows = (int)$request->input('rows', 10);
        $page = 1 + (int)$request->input('page', 0);

        \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $usuarios = Usuario::paginate($rows);

        return response()->json([
            'estatus' => 1,
            'data' => $usuarios->items(),
            'total' => $usuarios->total(),
        ]);
    }

    /**
     * Crear un nuevo usuario.
     */
    public function create(Request $request)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'apellidoP' => 'required|string|max:50',
            'apellidoM' => 'nullable|string|max:50',
            'correo' => 'required|email|unique:Usuario,correo',
            'password' => 'required|string|min:6',
            'id_rol' => 'required|exists:Rol,id_rol',
            'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => $validator->errors(),
            ], 422);
        }

        // Crear el usuario
        $usuario = new Usuario();
        $usuario->nombre = $request->nombre;
        $usuario->apellidoP = $request->apellidoP;
        $usuario->apellidoM = $request->apellidoM;
        $usuario->correo = $request->correo;
        $usuario->password = Hash::make($request->password);
        $usuario->id_rol = $request->id_rol;

        if ($request->hasFile('foto_perfil')) {
            $archivo = $request->file('foto_perfil');
            $ruta = $archivo->store('fotos_perfil', 'public');
            $usuario->foto_perfil = $ruta;
        }

        $usuario->save();

        return response()->json([
            'estatus' => 1,
            'mensaje' => 'Usuario registrado con éxito',
            'data' => $usuario,
        ], 201);
    }

    /**
     * Mostrar un usuario específico.
     */
    public function show(string $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Usuario no encontrado',
            ], 404);
        }

        return response()->json([
            'estatus' => 1,
            'data' => $usuario,
        ]);
    }
    
   /**
 * Actualizar un usuario - Usando POST con _method
 */
    public function update(Request $request, $id)
 {
    $usuario = Usuario::find($id);
    
    if (!$usuario) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'Usuario no encontrado'
        ], 404);
    }

    // Obtener datos directamente
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
    
    if ($request->filled('id_rol')) {
        $input['id_rol'] = (int) $request->input('id_rol');
    }

    // Si no hay datos para actualizar
    if (empty($input) && !$request->hasFile('foto_perfil')) {
        return response()->json([
            'estatus' => 0,
            'mensaje' => 'No se recibieron datos para actualizar',
            'debug' => [
                'all_data' => $request->all(),
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type')
            ]
        ], 400);
    }

    // Validación
    $rules = [];
    if (isset($input['nombre'])) $rules['nombre'] = 'required|string|max:50';
    if (isset($input['apellidoP'])) $rules['apellidoP'] = 'required|string|max:50';
    if (array_key_exists('apellidoM', $input)) $rules['apellidoM'] = 'nullable|string|max:50';
    if (isset($input['correo'])) $rules['correo'] = 'required|email|unique:Usuario,correo,'.$id.',id_usuario';
    if (isset($input['password'])) $rules['password'] = 'required|string|min:6';
    if (isset($input['id_rol'])) $rules['id_rol'] = 'required|exists:Rol,id_rol';

    if (!empty($rules)) {
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => $validator->errors()
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

    // Manejo de imagen
    if ($request->hasFile('foto_perfil')) {
        if ($usuario->foto_perfil && Storage::disk('public')->exists($usuario->foto_perfil)) {
            Storage::disk('public')->delete($usuario->foto_perfil);
        }
        $usuario->foto_perfil = $request->file('foto_perfil')->store('fotos_perfil', 'public');
    }

    $usuario->save();

    return response()->json([
        'estatus' => 1,
        'mensaje' => 'Usuario actualizado con éxito',
        'data' => $usuario->fresh()
    ]);
 }
    
   

    /**
     * Eliminar un usuario.
     */
    public function destroy($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Usuario no encontrado',
            ], 404);
        }

        // Eliminar la imagen de perfil si existe
        if ($usuario->foto_perfil && Storage::disk('public')->exists($usuario->foto_perfil)) {
            Storage::disk('public')->delete($usuario->foto_perfil);
        }

        $usuario->delete();

        return response()->json([
            'estatus' => 1,
            'mensaje' => 'Usuario eliminado con éxito',
        ]);
    }

    /**
     * Iniciar sesión.
     */
    public function login(Request $request)
    {
        // Validación de datos
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required',
        ]);

        // Buscar el usuario por correo
        $usuario = Usuario::where('correo', $request->correo)->first();

        if ($usuario && Hash::check($request->password, $usuario->password)) {
            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'estatus' => 1,
                'mensaje' => 'Inicio de sesión exitoso',
                'access_token' => $token,
                'data' => $usuario,
            ]);
        } else {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Credenciales incorrectas',
            ], 401);
        }
    }
}
