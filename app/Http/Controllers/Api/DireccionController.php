<?php

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Direccion;
use Illuminate\Validation\ValidationException;

class DireccionController extends Controller
{
    /**
     * Muestra una lista de todas las direcciones.
     */
    public function index()
    {
        return response()->json(Direccion::all(), 200);
    }

    /**
     * Guarda una nueva dirección en la base de datos.
     */
    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'calle' => 'required|string|max:100',
                'numero_int' => 'nullable|string|max:10',
                'numero_ext' => 'required|string|max:10',
                'colonia' => 'required|string|max:100',
                'codigo_postal' => 'required|string|size:5'
            ]);

            $direccion = Direccion::create($validatedData);
            return response()->json($direccion, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }

    /**
     * Muestra una dirección específica.
     */
    public function show($id)
    {
        $direccion = Direccion::find($id);

        if (!$direccion) {
            return response()->json(['error' => 'Dirección no encontrada'], 404);
        }

        return response()->json($direccion, 200);
    }

    /**
     * Actualiza una dirección en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $direccion = Direccion::find($id);

        if (!$direccion) {
            return response()->json(['error' => 'Dirección no encontrada'], 404);
        }

        try {
            $validatedData = $request->validate([
                'calle' => 'sometimes|string|max:100',
                'numero_int' => 'nullable|string|max:10',
                'numero_ext' => 'sometimes|string|max:10',
                'colonia' => 'sometimes|string|max:100',
                'codigo_postal' => 'sometimes|string|size:5'
            ]);

            $direccion->update($validatedData);
            return response()->json($direccion, 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }

    /**
     * Elimina una dirección de la base de datos.
     */
    public function destroy($id)
    {
        $direccion = Direccion::find($id);

        if (!$direccion) {
            return response()->json(['error' => 'Dirección no encontrada'], 404);
        }

        $direccion->delete();
        return response()->json(['message' => 'Dirección eliminada correctamente'], 200);
    }
}
