<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Usuario; // Cambiar de User a Usuario
use App\Models\PasswordReset;
use App\Services\PipedreamService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    private $pipedreamService;

    public function __construct(PipedreamService $pipedreamService)
    {
        $this->pipedreamService = $pipedreamService;
    }

    public function sendResetCode(ForgotPasswordRequest $request)
    {
        try {
            DB::beginTransaction();

            $correo = $request->correo;
            $usuario = Usuario::where('correo', $correo)->activos()->first(); // Solo usuarios activos

            if (!$usuario) {
                return response()->json([
                    'message' => 'Usuario no encontrado o inactivo',
                    'error' => 'user_not_found'
                ], 404);
            }

            // Verificar rate limiting (máximo 3 códigos por hora)
            $recentCodes = PasswordReset::where('correo', $correo)
                ->where('created_at', '>', Carbon::now()->subHour())
                ->count();

            if ($recentCodes >= 3) {
                return response()->json([
                    'message' => 'Has superado el límite de códigos por hora. Intenta más tarde.',
                    'error' => 'rate_limit_exceeded'
                ], 429);
            }

            // Eliminar códigos anteriores de este correo
            PasswordReset::where('correo', $correo)->delete();

            // Generar código de 6 dígitos
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Crear registro en base de datos
            $passwordReset = PasswordReset::create([
                'correo' => $correo,
                'token' => Hash::make($code),
                'created_at' => now(),
                'expires_at' => now()->addMinutes(15),
                'attempts' => 0
            ]);

            // Enviar email vía Pipedream usando nombre completo
            $this->pipedreamService->sendPasswordResetEmail(
                $usuario->correo,
                $usuario->nombre_completo, // Usar el accessor
                $code
            );

            DB::commit();

            return response()->json([
                'message' => 'Código de verificación enviado correctamente',
                'expires_in_minutes' => 15,
                'correo' => $correo
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'send_code_failed'
            ], 500);
        }
    }

    public function verifyCode(ResetPasswordRequest $request)
    {
        try {
            DB::beginTransaction();

            $correo = $request->correo;
            $code = $request->code;

            // Buscar el registro de reset
            $resetRecord = PasswordReset::where('correo', $correo)->first();

            if (!$resetRecord) {
                return response()->json([
                    'message' => 'No se encontró solicitud de restablecimiento para este correo',
                    'error' => 'reset_not_found'
                ], 404);
            }

            // Verificar si ha expirado
            if ($resetRecord->isExpired()) {
                $resetRecord->delete();
                return response()->json([
                    'message' => 'El código ha expirado. Solicita uno nuevo.',
                    'error' => 'code_expired'
                ], 400);
            }

            // Verificar intentos máximos
            if ($resetRecord->hasExceededAttempts()) {
                $resetRecord->delete();
                return response()->json([
                    'message' => 'Has superado el número máximo de intentos. Solicita un nuevo código.',
                    'error' => 'max_attempts_exceeded'
                ], 400);
            }

            // Verificar código
            if (!Hash::check($code, $resetRecord->token)) {
                $resetRecord->incrementAttempts();
                
                $remainingAttempts = 5 - $resetRecord->attempts;
                
                return response()->json([
                    'message' => "Código incorrecto. Te quedan {$remainingAttempts} intentos.",
                    'error' => 'invalid_code',
                    'remaining_attempts' => $remainingAttempts
                ], 400);
            }

            // Código válido - actualizar contraseña
            $usuario = Usuario::where('correo', $correo)->first();
            $usuario->update([
                'password' => Hash::make($request->password)
            ]);

            // Eliminar el registro de reset
            $resetRecord->delete();

            DB::commit();

            return response()->json([
                'message' => 'Contraseña actualizada correctamente',
                'usuario' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre_completo' => $usuario->nombre_completo,
                    'correo' => $usuario->correo
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => 'reset_failed'
            ], 500);
        }
    }

    // Método adicional para verificar estado de un código
    public function checkCodeStatus(Request $request)
    {
        $request->validate(['correo' => 'required|email']);
        
        $resetRecord = PasswordReset::where('correo', $request->correo)->first();
        
        if (!$resetRecord) {
            return response()->json([
                'exists' => false,
                'message' => 'No hay código pendiente para este correo'
            ]);
        }
        
        return response()->json([
            'exists' => true,
            'expires_at' => $resetRecord->expires_at,
            'remaining_attempts' => 5 - $resetRecord->attempts,
            'is_expired' => $resetRecord->isExpired()
        ]);
    }
}