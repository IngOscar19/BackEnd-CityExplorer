<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Lugar;
use App\Models\Direccion;
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


       $token = $request->bearerToken();
       $usuario = Auth::user();
       if (!$usuario) {
           return response()->json(['mensaje' => 'Usuario no autenticado.'], 401);
       }


       if (strcasecmp($usuario->rol->nombre, 'anunciante') !== 0) {
           return response()->json(['mensaje' => 'No autorizado. Solo los anunciantes pueden crear lugares.'], 403);
       }
      




       dd($usuario->rol->nombre, strtolower($usuario->rol->nombre));




       $request->validate([
           'paginaWeb' => 'nullable|url',
           'nombre' => 'required|string|max:100',
           'descripcion' => 'nullable|string',
           'dias_servicio' => 'nullable|array',
           'num_telefonico' => 'nullable|string|max:15',
           'horario_apertura' => 'nullable|date_format:H:i:s',
           'horario_cierre' => 'nullable|date_format:H:i:s',
           'id_categoria' => 'required|integer',
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


   if ($lugar->id_usuario !== $usuario->id_usuario) {
       return response()->json(['mensaje' => 'No autorizado. Solo el creador puede editar este lugar.'], 403);
   }


   try {
       DB::beginTransaction();


       // Validar campos anidados
       $request->validate([
           'direccion.calle' => 'required|string|max:100',
           'direccion.numero_int' => 'nullable|string|max:10',
           'direccion.numero_ext' => 'required|string|max:10',
           'direccion.colonia' => 'required|string|max:100',
           'direccion.codigo_postal' => 'required|string|size:5',


           'lugar.paginaWeb' => 'nullable|url',
           'lugar.nombre' => 'required|string|max:100',
           'lugar.descripcion' => 'nullable|string',
           'lugar.dias_servicio' => 'nullable|array',
           'lugar.num_telefonico' => 'nullable|string|max:15',
           'lugar.horario_apertura' => 'nullable|date_format:H:i:s',
           'lugar.horario_cierre' => 'nullable|date_format:H:i:s',
           'lugar.id_categoria' => 'required|integer',
           'lugar.activo' => 'boolean|nullable',
           'lugar.url' => 'required|string|max:255',
       ]);


       // Extraer datos anidados
       $direccionData = $request->input('direccion');
       $lugarData = $request->input('lugar');


       // Limpiar campos vacíos opcionales
       $direccionData = array_filter($direccionData, fn($v) => $v !== '');


       // Actualizar dirección asociada
       $direccion = Direccion::find($lugar->id_direccion);
       if ($direccion) {
           $direccion->update($direccionData);
       }


       // Actualizar lugar
       $lugar->update($lugarData);


       DB::commit();


       return response()->json([
           'mensaje' => 'Lugar y dirección actualizados correctamente',
           'lugar' => $lugar,
           'direccion' => $direccion
       ]);


   } catch (ValidationException $e) {
       DB::rollBack();
       return response()->json(['error' => $e->errors()], 422);
   } catch (\Exception $e) {
       DB::rollBack();
       return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
   }
}




   // Eliminar lugar (solo el creador puede hacerlo)
   public function destroy($id)
   {
       $lugar = Lugar::find($id);


       if (!$lugar) {
           return response()->json(['mensaje' => 'Lugar no encontrado'], 404);
       }


       if ($lugar->id_usuario !== Auth::id()) {
           return response()->json(['mensaje' => 'No autorizado. Solo el creador puede eliminar este lugar.'], 403);
       }


       $lugar->delete();
       return response()->json(['mensaje' => 'Lugar eliminado correctamente.']);
   }
  
   /**
    * Crear un lugar con una dirección existente
    */
   public function createWithDireccion(Request $request)
 {
   $usuario = Auth::user()->load('rol');


   if (strcasecmp($usuario->rol->nombre, 'anunciante') !== 0) {
       return response()->json(['mensaje' => 'No autorizado. Solo los anunciantes pueden crear lugares.'], 403);
   }


   try {
       DB::beginTransaction();


       // Validación
       $request->validate([
           'direccion.calle' => 'required|string|max:100',
           'direccion.numero_int' => 'nullable|string|max:10',
           'direccion.numero_ext' => 'required|string|max:10',
           'direccion.colonia' => 'required|string|max:100',
           'direccion.codigo_postal' => 'required|string|size:5',


           'lugar.paginaWeb' => 'nullable|url',
           'lugar.nombre' => 'required|string|max:100',
           'lugar.descripcion' => 'nullable|string',
           'lugar.dias_servicio' => 'nullable|array',
           'lugar.num_telefonico' => 'nullable|string|max:15',
           'lugar.horario_apertura' => 'nullable|date_format:H:i:s',
           'lugar.horario_cierre' => 'nullable|date_format:H:i:s',
           'lugar.id_categoria' => 'required|integer',
           'lugar.activo' => 'boolean|nullable',
           'lugar.url' => 'required|string|max:255',
       ]);


       // Extraer datos anidados
       $direccionData = $request->input('direccion');
       $lugarData = $request->input('lugar');


       // Limpiar campos vacíos opcionales
       $direccionData = array_filter($direccionData, fn($v) => $v !== '');


       $direccion = Direccion::create($direccionData);


       if (!$direccion || !$direccion->id_direccion) {
           throw new \Exception("No se pudo crear la dirección");
       }
      
      
       $lugar = Lugar::create(array_merge(
           $lugarData,
           [
               'id_usuario' => $usuario->id_usuario,
               'id_direccion' => $direccion->id_direccion




           ]
       ));


       DB::commit();


       return response()->json([
           'mensaje' => 'Dirección y lugar creados exitosamente',
           'direccion' => $direccion,
           'lugar' => $lugar
       ], 201);


   } catch (ValidationException $e) {
       DB::rollBack();
       return response()->json(['error' => $e->errors()], 422);
   } catch (\Exception $e) {
       DB::rollBack();
       return response()->json(['error' => 'Error al crear la dirección y el lugar: ' . $e->getMessage()], 500);
   }
 }


   // Obtener todos los lugares creados por el usuario autenticado
   public function misLugares()
{
   $usuario = Auth::user();


   if (!$usuario) {
       return response()->json(['mensaje' => 'Usuario no autenticado.'], 401);
   }


   $lugares = Lugar::where('id_usuario', $usuario->id)->get();


   return response()->json($lugares);
 }
 
}
