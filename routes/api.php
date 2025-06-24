<?php

use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Api\DireccionController;
use App\Http\Controllers\Api\CategoriaLugarController;
use App\Http\Controllers\Api\LugarController;
use App\Http\Controllers\Api\ComentarioController;
use App\Http\Controllers\Api\FavoritosController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\ListaController;
use App\Http\Controllers\Api\ListaLugarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Ruta de prueba
Route::post('hola', function() {
   return response()->json(['message' => 'Hello World!']);
});

// Ruta para el login (con nombre)
Route::post('user/login', [UsuarioController::class, 'login'])->name('login');

// Ruta para registrar un usuario (con nombre)
Route::post('user/register', [UsuarioController::class, 'create'])->name('register');

// 🔓 Ruta pública para obtener todos los lugares sin autenticación
Route::get('lugar', [LugarController::class, 'index']); // <-- ESTA ES LA NUEVA

// 🔓 Ruta pública para obtener un lugar específico sin autenticación
Route::get('lugar/{id}', [LugarController::class, 'show'])->whereNumber('id');

Route::get('direccion-publica/{id}', [DireccionController::class, 'show']);

Route::get('usuarios', [UsuarioController::class, 'index']);

Route::get('/lugar/{id}/comentarios',  [ComentarioController::class, 'comentariosPorLugar']);
Route::get('/lugar/{id}/estadisticas', [ComentarioController::class, 'estadisticasLugar']);

Route::get('categorias', [CategoriaLugarController::class, 'index']); // Obtener todas las categorías

Route::get('imagenes/{id}', [LugarController::class, 'getImagenes'])
    ->whereNumber('id'); // Obtener imágenes de un lugar específico
// Rutas para Categoria (nueva)
Route::prefix('categoria')->group(function() {
     
    Route::get('/{id}', [CategoriaLugarController::class, 'show'])  // Obtener una categoría específica
        ->whereNumber('id');
});

// Rutas para Direccion (nueva)
Route::prefix('direccion')->group(function() {
    Route::get('', [DireccionController::class, 'index']);  // Obtener todas las direcciones
    Route::get('/{id}', [DireccionController::class, 'show'])  // Obtener una dirección específica
        ->whereNumber('id');
});

use App\Models\Imagenes;

Route::get('/lugar/{id}/imagenes', function ($id) {
    $imagenes = Imagenes::where('id_lugar', $id)->get();

    if ($imagenes->isEmpty()) {
        return response()->json(['mensaje' => 'No se encontraron imágenes para este lugar.'], 404);
    }

    // Agrega la URL completa a cada imagen
    $imagenesConUrl = $imagenes->map(function ($img) {
        return [
            'id_imagen' => $img->id_imagen,
            'url' => asset('storage/' . $img->url), // genera la URL completa
        ];
    });

    return response()->json($imagenesConUrl);
});

// Grupo de rutas protegidas con autenticación Sanctum
Route::middleware(['auth:sanctum'])->group(function() {

   // Rutas para UsuarioController
   Route::prefix('usuario')->group(function() {
       Route::get('', [UsuarioController::class, 'index']);
       Route::post('', [UsuarioController::class, 'create']);
       Route::get('/{id}', [UsuarioController::class, 'show'])->whereNumber('id');
       Route::patch('/{id}', [UsuarioController::class, 'update'])->whereNumber('id');
       Route::delete('/{id}', [UsuarioController::class, 'destroy'])->whereNumber('id');
       Route::post('/{id}/update', [UsuarioController::class, 'update'])->whereNumber('id');
   });

   // Rutas para RolController
   Route::prefix('rol')->group(function() {
       Route::get('', [RolController::class, 'index']);
       Route::post('', [RolController::class, 'create']);
       Route::get('/{id}', [RolController::class, 'show'])->whereNumber('id');
       Route::patch('/{id}', [RolController::class, 'update'])->whereNumber('id');
       Route::delete('/{id}', [RolController::class, 'destroy'])->whereNumber('id');
   });

   // Rutas para DireccionController (ya existentes)
   Route::prefix('direccion')->group(function() {
       Route::get('', [DireccionController::class, 'index']);
       Route::post('', [DireccionController::class, 'create']);
       Route::get('/{id}', [DireccionController::class, 'show'])->whereNumber('id');
       Route::patch('/{id}', [DireccionController::class, 'update'])->whereNumber('id');
       Route::delete('/{id}', [DireccionController::class, 'destroy'])->whereNumber('id');

       Route::post('/crear-para-lugar', [DireccionController::class, 'createWithLugar'])
           ->name('direccion.crear_para_lugar');
   });

   // Rutas para CategoriaLugarController
   Route::prefix('categoria_lugar')->group(function() {
       Route::get('', [CategoriaLugarController::class, 'index']);
       Route::post('', [CategoriaLugarController::class, 'create']);
       Route::get('/{id}', [CategoriaLugarController::class, 'show'])->whereNumber('id');
       Route::patch('/{id}', [CategoriaLugarController::class, 'update'])->whereNumber('id');
       Route::delete('/{id}', [CategoriaLugarController::class, 'destroy'])->whereNumber('id');
   });

   // Rutas para LugarController (protegidas, excepto la nueva pública que ya movimos arriba)
   Route::prefix('lugar')->group(function() {
       Route::post('', [LugarController::class, 'store']);
       /* Route::get('/{id}', [LugarController::class, 'show'])->whereNumber('id'); */
       Route::patch('/{id}', [LugarController::class, 'update'])->whereNumber('id');
       Route::delete('/{id}', [LugarController::class, 'destroy'])->whereNumber('id');

       Route::post('/con-direccion', [LugarController::class, 'createWithDireccion'])
           ->name('lugar.crear_con_direccion');
   });

   // Rutas para ComentarioController
   Route::prefix('comentarios')->group(function () {
        Route::get('/', [ComentarioController::class, 'index']);
        Route::post('/', [ComentarioController::class, 'create']);
        Route::get('/{id}', [ComentarioController::class, 'show']);
        Route::put('/{id}', [ComentarioController::class, 'update']);
        Route::delete('/{id}', [ComentarioController::class, 'destroy']);
    
    // Rutas adicionales
       
    });

    Route::prefix('favoritos')->group(function () {
        Route::get('/', [FavoritosController::class, 'index']);
        Route::post('/', [FavoritosController::class, 'store']);
        Route::delete('/{id_lugar}', [FavoritosController::class, 'destroy']);
        
        // Rutas adicionales
        Route::get('/check/{id_lugar}', [FavoritosController::class, 'check']);
        Route::post('/toggle', [FavoritosController::class, 'toggle']);
        Route::get('/stats', [FavoritosController::class, 'stats']);
    });

    // Rutas para PagoController
    Route::prefix('pago')->group(function() {
        Route::get('', [PagoController::class, 'index']); // Obtener todos los pagos
        Route::post('/pagar', [PagoController::class, 'pagar'])->name('pago.pagar');
        Route::get('/{id}', [PagoController::class, 'show'])->whereNumber('id'); // Obtener pago por ID
        Route::delete('/{id}', [PagoController::class, 'destroy'])->whereNumber('id'); // Eliminar pago por ID
    });

   // Rutas para ListaController
   Route::prefix('lista')->group(function() {
       Route::get('', [ListaController::class, 'index']);
       Route::post('', [ListaController::class, 'store']);
       Route::get('/{id}', [ListaController::class, 'show'])->whereNumber('id');
       Route::patch('/{id}', [ListaController::class, 'update'])->whereNumber('id');
       Route::delete('/{id}', [ListaController::class, 'destroy'])->whereNumber('id');
   });

   // Rutas para ListaLugarController
   Route::prefix('lista_lugar')->group(function() {
       Route::get('', [ListaLugarController::class, 'index']);
       Route::post('', [ListaLugarController::class, 'store']);
       Route::get('/{id}', [ListaLugarController::class, 'show'])->whereNumber('id');
       Route::delete('/{id}', [ListaLugarController::class, 'destroy'])->whereNumber('id');
   });
});

// Obtener la autenticación del usuario
Route::get('/user', function (Request $request) {
   return response()->json($request->usuario());
})->middleware('auth:sanctum');
