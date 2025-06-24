<?php

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller;
use App\Models\CategoriaLugar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaLugarController extends Controller
{
    public function index(Request $request)
    {
        $categorias = CategoriaLugar::all();

        return response()->json([
        'estatus' => 1,
        'data' => $categorias,
        'total' => $categorias->count()
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nombre' => 'required|unique:categoria_lugares'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => $validator->errors()
            ]);
        }

        $categoria = new CategoriaLugar();
        $categoria->nombre = $request->nombre;
        $categoria->save();

        return response()->json([
            'estatus' => 1,
            'mensaje' => 'Categoría registrada'
        ]);
    }

    public function show($id)
    {
        $categoria = CategoriaLugar::find($id);

        if (!$categoria) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Categoría no encontrada'
            ]);
        }

        return response()->json([
            'estatus' => 1,
            'data' => $categoria
        ]);
    }

    public function update(Request $request, $id)
    {
        $categoria = CategoriaLugar::find($id);

        if (!$categoria) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Categoría no encontrada'
            ]);
        }

        $categoria->nombre = $request->nombre;
        $categoria->save();

        return response()->json([
            'estatus' => 1,
            'mensaje' => 'Categoría actualizada'
        ]);
    }

    public function destroy($id)
    {
        $categoria = CategoriaLugar::find($id);

        if (!$categoria) {
            return response()->json([
                'estatus' => 0,
                'mensaje' => 'Categoría no encontrada'
            ]);
        }

        $categoria->delete();

        return response()->json([
            'estatus' => 1,
            'mensaje' => 'Categoría eliminada'
        ]);
    }
}
