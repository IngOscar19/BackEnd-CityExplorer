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
use App\Http\Controllers\Api\EstadisticasVisitasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Imagenes;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\AdminController;


// Ruta para el login (con nombre)
Route::post('user/login', [UsuarioController::class, 'login'])->name('login');

// Ruta para registrar un usuario (con nombre)
Route::post('user/register', [UsuarioController::class, 'create'])->name('register');

// 🔓 Ruta pública para obtener todos los lugares sin autenticación
Route::get('lugar', [LugarController::class, 'index']); 

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

// 🔓 Rutas públicas para estadísticas de visitas
Route::prefix('estadisticas-visitas')->group(function() {
    Route::post('/', [EstadisticasVisitasController::class, 'registrarVisita']); // Registrar visita
    Route::get('/lugar/{id}', [EstadisticasVisitasController::class, 'obtenerEstadisticasLugar'])->whereNumber('id'); // Estadísticas de lugar
    Route::get('/lugares-populares', [EstadisticasVisitasController::class, 'obtenerLugaresMasVisitados']); 
});



Route::get('/lugar/{id}/imagenes', function ($id) {
    $imagenes = Imagenes::where('id_lugar', $id)->get();

    if ($imagenes->isEmpty()) {
        return response()->json(['mensaje' => 'No se encontraron imágenes para este lugar.'], 404);
    }

    // Agrega la URL completa a cada imagen (solo si es ruta relativa)
    $imagenesConUrl = $imagenes->map(function ($img) {
        return [
            'id_imagen' => $img->id_imagen,
            'url' => \Illuminate\Support\Str::startsWith($img->url, ['http://', 'https://'])
                ? $img->url
                : asset('storage/' . $img->url),
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
       Route::get('/{id}', [UsuarioController::class, 'show'])->whereNumber('id');
       Route::delete('/{id}', [UsuarioController::class, 'destroy'])->whereNumber('id');
       Route::post('/{id}/update', [UsuarioController::class, 'update'])->whereNumber('id');
   });

   // === GESTIÓN DE USUARIOS ===
   // Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);
    
    // Perfil y utilidades
    Route::get('/perfil', [UsuarioController::class, 'perfil']);
    Route::post('/logout', [UsuarioController::class, 'logout']);
    
    // === ADMINISTRACIÓN (solo admins) ===
    Route::prefix('admin')->group(function () {
        Route::get('/usuarios', [AdminController::class, 'index']);
        Route::get('/usuarios/{id}', [AdminController::class, 'show']);
        Route::put('/usuarios/{id}', [AdminController::class, 'update']);
        
        // Gestión de estados
        Route::post('/usuarios/{id}/bloquear', [AdminController::class, 'bloquearUsuario']);
        Route::post('/usuarios/{id}/desbloquear', [AdminController::class, 'desbloquearUsuario']);
        Route::post('/usuarios/{id}/toggle', [AdminController::class, 'toggleEstadoUsuario']);
        Route::get('/lugares', [AdminController::class, 'lugares']);
        Route::patch('/lugares/{id}/toggle', [AdminController::class, 'toggleEstadoLugar']); 
        Route::get('/estadisticas/lugares', [AdminController::class, 'estadisticasLugares']);
        Route::delete('/lugares/{id}', [AdminController::class, 'eliminarLugar']); // Eliminación lógica
        Route::patch('/lugares/{id}/restaurar', [AdminController::class, 'restaurarLugar']); // Restaurar eliminado
        
        
       
        Route::get('/usuarios/{id}/historial', [AdminController::class, 'historial']);
        
        // Estadísticas
        Route::get('/estadisticas', [AdminController::class, 'estadisticas']);
        
        // 🔐 Rutas de administración para estadísticas de visitas
        Route::prefix('estadisticas-visitas')->group(function() {
            Route::get('/resumen', [EstadisticasVisitasController::class, 'obtenerResumenGeneral']); // Resumen general del sistema
            Route::delete('/limpiar', [EstadisticasVisitasController::class, 'limpiarEstadisticasAntiguas']); // Limpiar estadísticas antiguas
        });
    });

    // Rutas existentes (mantenidas para compatibilidad)
        Route::post('/estadisticas-visitas', [EstadisticasVisitasController::class, 'registrarVisita']);
        Route::get('/estadisticas-visitas/lugar/{idLugar}', [EstadisticasVisitasController::class, 'obtenerEstadisticasPorLugar']);
        Route::get('/estadisticas/anunciante/{idUsuario}', [EstadisticasVisitasController::class, 'obtenerEstadisticasAnunciante']);

    
        Route::get('/lugar/{idLugar}/tiempo-promedio', [EstadisticasVisitasController::class, 'obtenerTiempoPromedioLugar']);
        Route::get('/lugar/{idLugar}/cantidad-visitas', [EstadisticasVisitasController::class, 'obtenerCantidadVisitasLugar']);

    
        Route::get('/anunciante/{idUsuario}/tiempo-promedio', [EstadisticasVisitasController::class, 'obtenerTiempoPromedioAnunciante']);
        Route::get('/anunciante/{idUsuario}/cantidad-visitas', [EstadisticasVisitasController::class, 'obtenerCantidadVisitasAnunciante']);
   
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
   });

   // Rutas para LugarController (protegidas, excepto la nueva pública que ya movimos arriba)
   Route::prefix('lugar')->group(function() {
       Route::get('/listaLugares', [LugarController::class, 'indexAll']);
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
    
    
       
    });

    Route::prefix('favoritos')->group(function () {
        Route::get('/', [FavoritosController::class, 'index']);
        Route::get('/check/{id_lugar}', [FavoritosController::class, 'check']);
        Route::post('/toggle', [FavoritosController::class, 'toggle']);
       
    });

    // Rutas para PagoController
    Route::prefix('pago')->group(function() {
        Route::get('', [PagoController::class, 'index']); // Obtener todos los pagos
        // Rutas de usuario
        Route::get('/mis-pagos', [PagoController::class, 'misPagos']); // Obtener pagos del usuario
        // Rutas de Stripe - Setup y configuración
        Route::post('/setup-intent', [PagoController::class, 'crearSetupIntent']); 
        Route::post('/pagar', [PagoController::class, 'pagar']); // Pago manual (con CVV)
        Route::post('/verificar-pago/{payment_intent_id}', [PagoController::class, 'verificarPago']); // Verificar estado de pago
        
    });

    
});



// Obtener la autenticación del usuario
Route::get('/user', function (Request $request) {
   return response()->json($request->usuario());
})->middleware('auth:sanctum');