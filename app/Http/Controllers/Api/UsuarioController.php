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
     * Actualizar un usuario.
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Usuario no encontrado',
            ], 404);
        }

        // Validación de datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:50',
            'apellidoP' => 'nullable|string|max:50',
            'apellidoM' => 'nullable|string|max:50',
            'correo' => 'nullable|email|unique:Usuario,correo,' . $id . ',id_usuario',
            'password' => 'nullable|string|min:6',
            'id_rol' => 'nullable|exists:Rol,id_rol',
            'foto_perfil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => $validator->errors(),
            ], 422);
        }

        // Actualizar campos
        if ($request->has('nombre')) $usuario->nombre = $request->nombre;
        if ($request->has('apellidoP')) $usuario->apellidoP = $request->apellidoP;
        if ($request->has('apellidoM')) $usuario->apellidoM = $request->apellidoM;
        if ($request->has('correo')) $usuario->correo = $request->correo;
        if ($request->has('password')) $usuario->password = Hash::make($request->password);
        if ($request->has('id_rol')) $usuario->id_rol = $request->id_rol;

        if ($request->hasFile('foto_perfil')) {
            // Eliminar la imagen anterior si existe
            if ($usuario->foto_perfil && Storage::disk('public')->exists($usuario->foto_perfil)) {
                Storage::disk('public')->delete($usuario->foto_perfil);
            }

            $archivo = $request->file('foto_perfil');
            $ruta = $archivo->store('fotos_perfil', 'public');
            $usuario->foto_perfil = $ruta;
        }

        $usuario->save();

        return response()->json([
            'estatus' => 1,
            'mensaje' => 'Usuario actualizado con éxito',
            'data' => $usuario,
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
