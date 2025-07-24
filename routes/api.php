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
use App\Models\Imagenes;
use App\Http\Controllers\Api\PasswordResetController;

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

Route::prefix('password')->group(function () {
    Route::post('/forgot', [PasswordResetController::class, 'sendResetCode']);
    Route::post('/reset', [PasswordResetController::class, 'verifyCode']);
    Route::post('/check-status', [PasswordResetController::class, 'checkCodeStatus']);
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
       Route::post('/{id}', [LugarController::class, 'update'])->whereNumber('id');
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
        // Rutas administrativas (solo para anunciantes)
        Route::get('', [PagoController::class, 'index']); // Obtener todos los pagos
        Route::get('/{id}', [PagoController::class, 'show'])->whereNumber('id'); // Obtener pago por ID
        Route::delete('/{id}', [PagoController::class, 'destroy'])->whereNumber('id'); // Eliminar pago por ID
        
        // Rutas de usuario
        Route::get('/mis-pagos', [PagoController::class, 'misPagos']); // Obtener pagos del usuario
        
        // Rutas de Stripe - Setup y configuración
        Route::post('/setup-intent', [PagoController::class, 'crearSetupIntent']); // Crear setup intent
        Route::post('/guardar-metodo', [PagoController::class, 'guardarMetodoPago']); // Guardar método de pago
        
        // Rutas de gestión de tarjetas
        Route::get('/tarjeta-guardada', [PagoController::class, 'obtenerTarjetaGuardada']); // Ver tarjeta guardada
        Route::delete('/tarjeta-guardada', [PagoController::class, 'eliminarTarjetaGuardada']); // Eliminar tarjeta guardada
        
        // Rutas de procesamiento de pagos
        Route::post('/pagar', [PagoController::class, 'pagar']); // Pago manual (con CVV)
        Route::post('/domiciliado', [PagoController::class, 'pagarDomiciliado']); // Pago domiciliado (sin CVV)
        
        // Rutas adicionales de utilidad
        //Route::get('/historial/{id_lugar}', [PagoController::class, 'historialPagosLugar'])->whereNumber('id_lugar'); // Historial de pagos por lugar
        Route::post('/verificar-pago/{payment_intent_id}', [PagoController::class, 'verificarPago']); // Verificar estado de pago
        //Route::get('/estadisticas', [PagoController::class, 'estadisticasPagos']); // Estadísticas de pagos del usuario
        Route::post('/reembolsar/{id}', [PagoController::class, 'reembolsar'])->whereNumber('id'); // Reembolsar pago (solo anunciantes)
    });

    

    

   
});

// Obtener la autenticación del usuario
Route::get('/user', function (Request $request) {
   return response()->json($request->usuario());
})->middleware('auth:sanctum');
