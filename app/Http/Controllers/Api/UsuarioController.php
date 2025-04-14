<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario; // Usa el modelo correcto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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

        $usuarios = Usuario::paginate($rows); // Usa el modelo correcto

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
        $usuario->password = Hash::make($request->password); // Hash de la contraseña
        $usuario->id_rol = $request->id_rol;
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
        $usuario = Usuario::find($id); // Usa el modelo correcto

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
        $usuario = Usuario::find($id); // Usa el modelo correcto

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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => $validator->errors(),
            ], 422);
        }

        // Actualizar campos
        if ($request->has('nombre')) {
            $usuario->nombre = $request->nombre;
        }
        if ($request->has('apellidoP')) {
            $usuario->apellidoP = $request->apellidoP;
        }
        if ($request->has('apellidoM')) {
            $usuario->apellidoM = $request->apellidoM;
        }
        if ($request->has('correo')) {
            $usuario->correo = $request->correo;
        }
        if ($request->has('password')) {
            $usuario->contraseña = Hash::make($request->contraseña);
        }
        if ($request->has('id_rol')) {
            $usuario->id_rol = $request->id_rol;
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
        $usuario = Usuario::find($id); // Usa el modelo correcto

        if (!$usuario) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Usuario no encontrado',
            ], 404);
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
        $usuario = Usuario::where('correo', $request->correo)->first(); // Usa el modelo correcto

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

    /**
 * Mostrar un usuario específico por correo electrónico.
 */
    public function showByCorreo(string $correo)
    {
         $usuario = Usuario::where('correo', $correo)->first();

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

}
