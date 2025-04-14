<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lugar;
use App\Models\Direccion;
use App\Models\CategoriaLugar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LugarController extends Controller
{
    // Listar todos los lugares
    public function index()
    {
        return response()->json(Lugar::all());
    }

    // Mostrar un solo lugar
    public function show($id)
    {
        $lugar = Lugar::find($id);
        if (!$lugar) {
            return response()->json(['mensaje' => 'Lugar no encontrado'], 404);
        }

        return response()->json($lugar);
    }

    // Crear un nuevo lugar (solo anunciantes)
    public function store(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no autenticado.'], 401);
        }

        if (strcasecmp($usuario->rol->nombre, 'anunciante') !== 0) {
            return response()->json(['mensaje' => 'No autorizado. Solo los anunciantes pueden crear lugares.'], 403);
        }

        $request->validate([
            'paginaWeb' => 'nullable|url',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'dias_servicio' => 'nullable|array',
            'num_telefonico' => 'nullable|string|max:15',
            'horario_apertura' => 'nullable|date_format:H:i:s',
            'horario_cierre' => 'nullable|date_format:H:i:s',
            'id_categoria' => 'required|integer|exists:categorias_lugares,id',
            'id_direccion' => 'required|integer|exists:direcciones,id',
            'activo' => 'boolean',
            'url' => 'required|string|max:255',
        ]);

        $lugar = Lugar::create(array_merge(
            $request->all(),
            ['id_usuario' => $usuario->id]
        ));

        return response()->json($lugar, 201);
    }

    // Actualizar lugar (solo el creador puede hacerlo)
    public function update(Request $request, $id)
    {
        $usuario = Auth::user();
        $lugar = Lugar::find($id);

        if (!$lugar) {
            return response()->json(['mensaje' => 'Lugar no encontrado'], 404);
        }

        if ($lugar->id_usuario !== $usuario->id) {
            return response()->json(['mensaje' => 'No autorizado. Solo el creador puede editar este lugar.'], 403);
        }

        try {
            DB::beginTransaction();

            $request->validate([
                // Validaciones para dirección
                'direccion.calle' => 'required|string|max:100',
                'direccion.numero_int' => 'nullable|string|max:10',
                'direccion.numero_ext' => 'required|string|max:10',
                'direccion.colonia' => 'required|string|max:100',
                'direccion.codigo_postal' => 'required|string|size:5',

                // Validaciones para lugar
                'lugar.paginaWeb' => 'nullable|url',
                'lugar.nombre' => 'required|string|max:100',
                'lugar.descripcion' => 'nullable|string',
                'lugar.dias_servicio' => 'nullable|array',
                'lugar.num_telefonico' => 'nullable|string|max:15',
                'lugar.horario_apertura' => 'nullable|date_format:H:i:s',
                'lugar.horario_cierre' => 'nullable|date_format:H:i:s',
                'lugar.id_categoria' => 'required|integer|exists:categorias_lugares,id',
                'lugar.activo' => 'boolean|nullable',
                'lugar.url' => 'required|string|max:255',
            ]);

            // Actualizar dirección asociada
            $direccionData = array_filter($request->input('direccion', []));
            $direccion = Direccion::find($lugar->id_direccion);
            if ($direccion) {
                $direccion->update($direccionData);
            }

            // Actualizar lugar
            $lugarData = array_filter($request->input('lugar', []));
            $lugar->update($lugarData);

            DB::commit();

            return response()->json([
                'mensaje' => 'Lugar y dirección actualizados correctamente.',
                'lugar' => $lugar,
                'direccion' => $direccion,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => "Error al actualizar: {$e->getMessage()}"], 500);
        }
    }

    // Eliminar lugar (solo el creador puede hacerlo)
    public function destroy($id)
    {
        $usuario = Auth::user();
        $lugar = Lugar::find($id);

        if (!$lugar) {
            return response()->json(['mensaje' => "Lugar no encontrado"], 404);
        }

        if ($lugar->id_usuario !== $usuario->id) {
            return response()->json(['mensaje' => "No autorizado"], 403);
        }

        $lugar->delete();
        return response()->json(['mensaje' => "Lugar eliminado correctamente"]);
    }

    // Obtener todos los lugares creados por el usuario autenticado
    public function misLugares()
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['mensaje' => "Usuario no autenticado"], 401);
        }

        $lugares = Lugar::where('id_usuario', $usuario->id)->get();
        return response()->json($lugares);
    }

    // Obtener lugares por categoría
    public function porCategoria($id_categoria)
    {
        // Verificar si la categoría existe
        $categoria = CategoriaLugar::find($id_categoria);
        if (!$categoria) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }
    
        // Obtener lugares activos de la categoría
        $lugares = Lugar::with('categoria')
                        ->where('id_categoria', $id_categoria) // Asegúrate de que este campo exista en la tabla "lugares"
                        ->where('activo', true)
                        ->get();
    
        return response()->json($lugares);
    }
    
}
