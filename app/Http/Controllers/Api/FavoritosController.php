<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favoritos;


class FavoritosController extends Controller
{
    /**
     * Display a listing of the favoritos.
     */
    public function index()
    {
        $favoritos = Favorito::all();
        return response()->json($favoritos);
    }

    /**
     * Store a newly created favorito in storage.
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified favorito.
     */
    public function show($id)
    {
        
    }

    /**
     * Remove the specified favorito from storage.
     */
    public function destroy($id)
    {
    }
}