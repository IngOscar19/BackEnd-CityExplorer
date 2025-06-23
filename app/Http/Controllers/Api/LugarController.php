<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lugar;
use App\Models\Direccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Imagenes;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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
           'id_categoria' => 'required|integer',
           'id_direccion' => 'required|integer|exists:direcciones,id_direccion',
           'activo' => 'boolean',
           'imagenes.*' => 'imagenes|mimes:jpg,jpeg,png,webp|max:2048',
       ]);

       DB::beginTransaction();

       try {
           $lugar = Lugar::create(array_merge(
               $request->except('imagenes'),
               ['id_usuario' => $usuario->id_usuario]
           ));

           if ($request->hasFile('imagenes')) {
               foreach ($request->file('imagenes') as $file) {
                   $image = Image::make($file)->resize(1200, null, function ($constraint) {
                       $constraint->aspectRatio();
                       $constraint->upsize();
                   });

                   $fileName = uniqid('img_') . '.jpg';
                   $path = 'imagenes_lugares/' . $fileName;

                   Storage::disk('public')->put($path, (string) $image->encode('jpg', 80));

                   Imagenes::create([
                       'id_lugar' => $lugar->id_lugar,
                       'url' => $path,
                   ]);
               }
           }

           DB::commit();

           return response()->json([
               'mensaje' => 'Lugar creado correctamente',
               'lugar' => $lugar->load('imagenes')
           ], 201);

       } catch (\Exception $e) {
           DB::rollBack();
           return response()->json(['error' => 'Error al crear lugar: ' . $e->getMessage()], 500);
       }
   }

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

               'imagenes.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
               'imagenes_a_eliminar' => 'array',
               'imagenes_a_eliminar.*' => 'integer|exists:imagenes,id_imagen,id_lugar,' . $lugar->id_lugar,
           ]);

           $direccion = Direccion::find($lugar->id_direccion);
           if ($direccion) {
               $direccionData = array_filter($request->input('direccion'), fn($v) => $v !== '');
               $direccion->update($direccionData);
           }

           $lugarData = array_filter($request->input('lugar'), fn($v) => $v !== '');
           $lugar->update($lugarData);

           if ($request->has('imagenes_a_eliminar')) {
               $imagenes = Imagenes::whereIn('id_imagen', $request->imagenes_a_eliminar)
                                 ->where('id_lugar', $lugar->id_lugar)
                                 ->get();

               foreach ($imagenes as $imagen) {
                   Storage::disk('public')->delete($imagen->url);
                   $imagen->delete();
               }
           }

           if ($request->hasFile('imagenes')) {
               foreach ($request->file('imagenes') as $file) {
                   $image = Image::make($file)->resize(1200, null, function ($constraint) {
                       $constraint->aspectRatio();
                       $constraint->upsize();
                   });

                   $fileName = uniqid('img_') . '.jpg';
                   $path = 'imagenes_lugares/' . $fileName;

                   Storage::disk('public')->put($path, (string) $image->encode('jpg', 80));

                   Imagenes::create([
                       'id_lugar' => $lugar->id_lugar,
                       'url' => $path,
                   ]);
               }
           }

           DB::commit();

           return response()->json([
               'mensaje' => 'Lugar actualizado correctamente',
               'lugar' => $lugar->load('imagenes', 'direccion')
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

               
               // Validación para imágenes
               'imagenes.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
           ]);

           // Extraer datos anidados
           $direccionData = $request->input('direccion');
           $lugarData = $request->input('lugar');

           // Limpiar campos vacíos opcionales
           $direccionData = array_filter($direccionData, fn($v) => $v !== '');

           // Crear dirección
           $direccion = Direccion::create($direccionData);

           if (!$direccion || !$direccion->id_direccion) {
               throw new \Exception("No se pudo crear la dirección");
           }

           // Crear lugar
           $lugar = Lugar::create(array_merge(
               $lugarData,
               [
                   'id_usuario' => $usuario->id_usuario,
                   'id_direccion' => $direccion->id_direccion
               ]
           ));

           // Procesar imágenes si están presentes
           if ($request->hasFile('imagenes')) {
               foreach ($request->file('imagenes') as $file) {
                   // Redimensionar y optimizar imagen
                   $image = Image::make($file)->resize(1200, null, function ($constraint) {
                       $constraint->aspectRatio();
                       $constraint->upsize();
                   });

                   // Generar nombre único para el archivo
                   $fileName = uniqid('img_') . '.jpg';
                   $path = 'imagenes_lugares/' . $fileName;

                   // Guardar imagen optimizada en storage
                   Storage::disk('public')->put($path, (string) $image->encode('jpg', 80));

                   // Crear registro en base de datos
                   Imagenes::create([
                       'id_lugar' => $lugar->id_lugar,
                       'url' => $path,
                   ]);
               }
           }

           DB::commit();

           return response()->json([
               'mensaje' => 'Dirección y lugar creados exitosamente',
               'direccion' => $direccion,
               'lugar' => $lugar->load('imagenes') // Cargar las imágenes en la respuesta
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

       $lugares = Lugar::where('id_usuario', $usuario->id_usuario)->get();

       return response()->json($lugares);
   }
}