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
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class LugarController extends Controller
{
    // Constante para el límite de imágenes
    const LIMITE_IMAGENES = 8;

    // Listar todos los lugares
    public function index()
    {
        return response()->json(Lugar::with('imagenes')->get());
    }

    // Mostrar un solo lugar con sus imágenes
    public function show($id)
    {
        $lugar = Lugar::with('imagenes')->find($id);
        
        if (!$lugar) {
            return response()->json(['mensaje' => 'Lugar no encontrado'], 404);
        }

        // Transformar URLs de imágenes para incluir el path completo
        $lugar->imagenes->transform(function($imagen) {
            $imagen->url = asset("storage/{$imagen->url}");
            return $imagen;
        });

        return response()->json($lugar);
    }

    // Crear un nuevo lugar con imágenes
    public function store(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['mensaje' => 'Usuario no autenticado.'], 401);
        }

        if (strcasecmp($usuario->rol->nombre, 'anunciante') !== 0) {
            return response()->json(['mensaje' => 'No autorizado. Solo los anunciantes pueden crear lugares.'], 403);
        }

        $validator = Validator::make($request->all(), [
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
            'imagenes' => 'nullable|array|max:' . self::LIMITE_IMAGENES,
            'imagenes.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Validar duplicados
        $duplicadoValidation = $this->validarLugarDuplicado($request->nombre, $request->id_direccion);
        if ($duplicadoValidation) {
            return $duplicadoValidation;
        }

        DB::beginTransaction();

        try {
            // Crear el lugar
            $lugar = Lugar::create(array_merge(
                $request->except('imagenes'),
                ['id_usuario' => $usuario->id_usuario]
            ));

            // Procesar imágenes que ya existen
            if ($request->has('imagenes')) {
                $this->procesarImagenes($lugar, $request->file('imagenes'));
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

    // Actualizar lugar con gestión avanzada de imágenes
    public function update(Request $request, $id)
    {
        $usuario = Auth::user();
        $lugar = Lugar::with('imagenes')->find($id);

        if (!$lugar) {
            return response()->json(['mensaje' => 'Lugar no encontrado'], 404);
        }

        if ($lugar->id_usuario !== $usuario->id_usuario) {
            return response()->json(['mensaje' => 'No autorizado. Solo el creador puede editar este lugar.'], 403);
        }

        DB::beginTransaction();

        try {
            // Validar límite de imágenes ANTES de cualquier operación
            $validacionImagenes = $this->validarLimiteImagenesActualizacion($lugar, $request);
            if ($validacionImagenes) {
                return $validacionImagenes;
            }

            // Validación y preparación de datos
            $data = $this->prepararDatosActualizacion($request);
            $validator = $this->validarDatosActualizacion($data);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // Validar duplicados si se está cambiando el nombre
            if (isset($data['lugar']['nombre']) && $data['lugar']['nombre'] !== $lugar->nombre) {
                $duplicadoValidation = $this->validarLugarDuplicado($data['lugar']['nombre'], $lugar->id_direccion, $id);
                if ($duplicadoValidation) {
                    return $duplicadoValidation;
                }
            }

            // Actualizar dirección
            if ($lugar->id_direccion && isset($data['direccion'])) {
                $direccion = Direccion::find($lugar->id_direccion);
                if ($direccion) {
                    $direccion->update($data['direccion']);
                }
            }

            // Actualizar lugar
            if (isset($data['lugar'])) {
                $lugar->update($data['lugar']);
            }

            // Manejo de imágenes
            $this->gestionarImagenes($lugar, $request);

            DB::commit();

            return response()->json([
                'mensaje' => 'Lugar actualizado correctamente',
                'lugar' => $lugar->fresh()->load('imagenes', 'direccion')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    // Eliminar lugar
    public function destroy($id)
    {
        $lugar = Lugar::with('imagenes')->find($id);

        if (!$lugar) {
            return response()->json(['mensaje' => 'Lugar no encontrado'], 404);
        }

        if ($lugar->id_usuario !== Auth::id()) {
            return response()->json(['mensaje' => 'No autorizado. Solo el creador puede eliminar este lugar.'], 403);
        }

        DB::beginTransaction();

        try {
            // Eliminar imágenes físicas
            foreach ($lugar->imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->url);
            }

            // Eliminar el lugar (las imágenes se borrarán en cascada por la relación)
            $lugar->delete();

            DB::commit();

            return response()->json(['mensaje' => 'Lugar eliminado correctamente.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar lugar: ' . $e->getMessage()], 500);
        }
    }

    // Métodos auxiliares protegidos

    /**
     * Valida el límite de imágenes durante la actualización
     */
    protected function validarLimiteImagenesActualizacion(Lugar $lugar, Request $request)
    {
        // Contar imágenes actuales
        $imagenesActuales = $lugar->imagenes->count();
        
        // Contar imágenes a eliminar
        $imagenesAEliminar = 0;
        if ($request->has('imagenes_a_eliminar')) {
            $imagenesAEliminar = is_array($request->imagenes_a_eliminar) 
                ? count($request->imagenes_a_eliminar)
                : count(explode(',', $request->imagenes_a_eliminar));
        }

        // Contar nuevas imágenes
        $nuevasImagenes = 0;
        if ($request->hasFile('nuevas_imagenes')) {
            $nuevasImagenes = count($request->file('nuevas_imagenes'));
        } elseif ($request->hasFile('imagenes')) {
            $nuevasImagenes = count($request->file('imagenes'));
        }

        // Calcular total final
        $totalFinal = $imagenesActuales - $imagenesAEliminar + $nuevasImagenes;

        if ($totalFinal > self::LIMITE_IMAGENES) {
            return response()->json([
                'error' => [
                    'imagenes' => [
                        sprintf(
                            'El lugar excedería el límite de %d imágenes. Actualmente tiene %d imágenes, se eliminarán %d y se añadirán %d, resultando en %d imágenes totales.',
                            self::LIMITE_IMAGENES,
                            $imagenesActuales,
                            $imagenesAEliminar,
                            $nuevasImagenes,
                            $totalFinal
                        )
                    ]
                ]
            ], 422);
        }

        // Validar que las nuevas imágenes no excedan el límite por sí solas
        if ($nuevasImagenes > self::LIMITE_IMAGENES) {
            return response()->json([
                'error' => [
                    'imagenes' => [
                        sprintf('No puedes subir más de %d imágenes de una vez.', self::LIMITE_IMAGENES)
                    ]
                ]
            ], 422);
        }

        return null;
    }

    /**
     * Valida si existe un lugar duplicado
     */
    protected function validarLugarDuplicado($nombre, $idDireccion, $excluirId = null)
    {
        $query = Lugar::where('nombre', 'LIKE', trim($nombre))
                     ->where('id_direccion', $idDireccion);

        if ($excluirId) {
            $query->where('id_lugar', '!=', $excluirId);
        }

        $lugarExistente = $query->first();

        if ($lugarExistente) {
            return response()->json([
                'error' => [
                    'duplicado' => ['Ya existe un lugar con el mismo nombre en esta dirección.']
                ]
            ], 422);
        }

        return null;
    }

    /**
     * Valida si existe un lugar duplicado usando datos de dirección
     */
    protected function validarLugarDuplicadoPorDireccion($nombre, $direccionData, $excluirId = null)
    {
        // Buscar direcciones similares
        $direccionesSimilares = Direccion::where('calle', 'LIKE', trim($direccionData['calle']))
                                        ->where('numero_ext', trim($direccionData['numero_ext']))
                                        ->where('colonia', 'LIKE', trim($direccionData['colonia']))
                                        ->where('codigo_postal', trim($direccionData['codigo_postal']))
                                        ->pluck('id_direccion');

        if ($direccionesSimilares->isEmpty()) {
            return null; // No hay direcciones similares
        }

        // Buscar lugares con el mismo nombre en direcciones similares
        $query = Lugar::where('nombre', 'LIKE', trim($nombre))
                     ->whereIn('id_direccion', $direccionesSimilares);

        if ($excluirId) {
            $query->where('id_lugar', '!=', $excluirId);
        }

        $lugarExistente = $query->first();

        if ($lugarExistente) {
            return response()->json([
                'error' => [
                    'duplicado' => ['Ya existe un lugar con el mismo nombre en una dirección similar.']
                ]
            ], 422);
        }

        return null;
    }

    /**
     * Procesa y guarda imágenes para un lugar
     */
    protected function procesarImagenes(Lugar $lugar, $imagenes)
    {
        foreach ($imagenes as $file) {
            // Generar hash del contenido para evitar duplicados
            $fileHash = md5_file($file->path());
            
            // Verificar si ya existe una imagen idéntica para este lugar
            $imagenExistente = $lugar->imagenes()
                ->where('url', 'like', '%'.$fileHash.'%')
                ->first();
            
            if ($imagenExistente) {
                continue; // Saltar imagen duplicada
            }

            // Procesar imagen
            $image = Image::make($file)->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Generar nombre único con hash para evitar colisiones
            $fileName = 'img_'.$fileHash.'_'.time().'.jpg';
            $path = 'imagenes_lugares/'.$fileName;

            // Guardar imagen optimizada
            Storage::disk('public')->put($path, (string) $image->encode('jpg', 80));

            // Crear registro en BD
            Imagenes::create([
                'id_lugar' => $lugar->id_lugar,
                'url' => $path,
            ]);
        }
    }

    /**
     * Gestiona las imágenes durante una actualización
     */
    protected function gestionarImagenes(Lugar $lugar, Request $request)
    {
        // Eliminar imágenes marcadas para borrado
        if ($request->has('imagenes_a_eliminar')) {
            $imagenesAEliminar = is_array($request->imagenes_a_eliminar) 
                ? $request->imagenes_a_eliminar 
                : explode(',', $request->imagenes_a_eliminar);
            
            $imagenes = Imagenes::whereIn('id_imagen', $imagenesAEliminar)
                ->where('id_lugar', $lugar->id_lugar)
                ->get();

            foreach ($imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->url);
                $imagen->delete();
            }
        }

        // Añadir nuevas imágenes
        if ($request->hasFile('nuevas_imagenes')) {
            $this->procesarImagenes($lugar, $request->file('nuevas_imagenes'));
        } elseif ($request->hasFile('imagenes')) {
            $this->procesarImagenes($lugar, $request->file('imagenes'));
        }
    }

    /**
     * Prepara los datos para actualización
     */
    protected function prepararDatosActualizacion(Request $request)
    {
        $input = $request->all();
        
        return [
            'direccion' => [
                'calle' => $input['direccion']['calle'] ?? $input['direction']['calle'] ?? $input['calle'] ?? null,
                'numero_ext' => $input['direccion']['numero_ext'] ?? $input['direction']['numero_ext'] ?? $input['numero_ext'] ?? null,
                'numero_int' => $input['direccion']['numero_int'] ?? $input['direction']['numero_int'] ?? $input['numero_int'] ?? null,
                'colonia' => $input['direccion']['colonia'] ?? $input['direction']['colonia'] ?? $input['colonia'] ?? null,
                'codigo_postal' => $input['direccion']['codigo_postal'] ?? $input['direction']['codigo_postal'] ?? $input['codigo_postal'] ?? null,
            ],
            'lugar' => [
                'nombre' => $input['lugar']['nombre'] ?? $input['nombre'] ?? null,
                'descripcion' => $input['lugar']['descripcion'] ?? $input['descripcion'] ?? null,
                'paginaWeb' => $input['lugar']['paginaWeb'] ?? $input['paginaWeb'] ?? null,
                'dias_servicio' => $input['lugar']['dias_servicio'] ?? $input['dias_servicio'] ?? [],
                'num_telefonico' => $input['lugar']['num_telefonico'] ?? $input['num_telefonico'] ?? null,
                'horario_apertura' => $input['lugar']['horario_apertura'] ?? $input['horario_apertura'] ?? null,
                'horario_cierre' => $input['lugar']['horario_cierre'] ?? $input['horario_cierre'] ?? null,
                'id_categoria' => $input['lugar']['id_categoria'] ?? $input['id_categoria'] ?? null,
                'activo' => $input['lugar']['activo'] ?? $input['activo'] ?? null,
            ]
        ];
    }

    /**
     * Valida los datos para actualización
     */
    protected function validarDatosActualizacion($data)
    {
        return Validator::make($data, [
            'direccion.calle' => 'sometimes|required|string|max:100',
            'direccion.numero_ext' => 'sometimes|required|string|max:10',
            'direccion.numero_int' => 'nullable|string|max:10',
            'direccion.colonia' => 'sometimes|required|string|max:100',
            'direccion.codigo_postal' => 'sometimes|required|string|size:5',
            
            'lugar.nombre' => 'sometimes|required|string|max:100',
            'lugar.id_categoria' => 'sometimes|required|integer',
            'lugar.paginaWeb' => 'nullable|url',
            'lugar.descripcion' => 'nullable|string',
            'lugar.dias_servicio' => 'nullable|array',
            'lugar.num_telefonico' => 'nullable|string|max:15',
            'lugar.horario_apertura' => 'nullable|date_format:H:i:s',
            'lugar.horario_cierre' => 'nullable|date_format:H:i:s',
            'lugar.activo' => 'nullable|boolean',
        ]);
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

        DB::beginTransaction();

        try {
            // Validación del límite de imágenes antes de procesar
            if ($request->hasFile('imagenes') && count($request->file('imagenes')) > self::LIMITE_IMAGENES) {
                return response()->json([
                    'error' => sprintf('No se pueden subir más de %d imágenes por lugar', self::LIMITE_IMAGENES)
                ], 422);
            }

            // Validación original
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
                
                // Validación para imágenes usando la constante
                'imagenes' => 'nullable|array|max:' . self::LIMITE_IMAGENES,
                'imagenes.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            // Extraer datos anidados
            $direccionData = $request->input('direccion');
            $lugarData = $request->input('lugar');

            // Validar duplicados antes de crear la dirección
            $duplicadoValidation = $this->validarLugarDuplicadoPorDireccion($lugarData['nombre'], $direccionData);
            if ($duplicadoValidation) {
                return $duplicadoValidation;
            }

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
                // Verificación redundante por seguridad
                if (count($request->file('imagenes')) > self::LIMITE_IMAGENES) {
                    throw new \Exception("Límite de imágenes excedido");
                }

                $this->procesarImagenes($lugar, $request->file('imagenes'));
            }

            DB::commit();

            return response()->json([
                'mensaje' => 'Dirección y lugar creados exitosamente',
                'direccion' => $direccion,
                'lugar' => $lugar->load('imagenes')
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

    public function getImagenes($id)
    {
        $imagenes = Imagenes::where('id_lugar', $id)->get()->map(function ($imagen) {
            return [
                'id_imagen' => $imagen->id_imagen,
                'url' => asset("storage/{$imagen->url}")
            ];
        });

        return response()->json($imagenes);
    }
}