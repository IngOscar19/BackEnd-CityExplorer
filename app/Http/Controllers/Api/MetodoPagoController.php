<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MetodoPago;



class MetodoPagoController extends Controller
{
    /**
     * Display a listing of the payment methods.
     */
    public function index()
    {
        $metodosPago = MetodoPago::all();
        return response()->json($metodosPago);
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $metodoPago = MetodoPago::create($validatedData);
        return response()->json($metodoPago, 201);
    }

    /**
     * Display the specified payment method.
     */
    public function show($id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado'], 404);
        }

        return response()->json($metodoPago);
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(Request $request, $id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $metodoPago->update($validatedData);
        return response()->json($metodoPago);
    }

    /**
     * Remove the specified payment method from storage.
     */
    public function destroy($id)
    {
        $metodoPago = MetodoPago::find($id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Método de pago no encontrado'], 404);
        }

        $metodoPago->delete();
        return response()->json(['message' => 'Método de pago eliminado correctamente']);
    }
}