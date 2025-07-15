<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lugar;
use App\Models\Pago;
use App\Models\MetodoPago;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\SetupIntent;
use Stripe\PaymentMethod;
use Stripe\PaymentIntent;
use Stripe\Charge;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;

class PagoController extends Controller
{
    const PRECIO_FIJO = 10000; // $100.00 MXN

    /**
     * Crear SetupIntent para guardar tarjeta (mejorado)
     */
    public function crearSetupIntent(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $usuario = $request->user();

        try {
            // Crear o obtener customer de Stripe
            if (!$usuario->stripe_customer_id) {
                $customer = Customer::create([
                    'email' => $usuario->correo,
                    'name' => $usuario->nombre ?? null,
                    'metadata' => [
                        'user_id' => $usuario->id_usuario,
                    ],
                ]);
                $usuario->stripe_customer_id = $customer->id;
                $usuario->save();
            }

            $setupIntent = SetupIntent::create([
                'customer' => $usuario->stripe_customer_id,
                'usage' => 'off_session', // Importante para domiciliado
                'payment_method_types' => ['card'],
            ]);

            return response()->json([
                'clientSecret' => $setupIntent->client_secret,
                'customerId' => $usuario->stripe_customer_id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear SetupIntent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Guardar método de pago en Stripe y en la base de datos (mejorado)
     */
    public function guardarMetodoPago(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'customer_id' => 'required|string',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentMethod = PaymentMethod::retrieve($request->payment_method);
            
            // Verificar que el payment method no esté ya attached
            if (!$paymentMethod->customer) {
                $paymentMethod->attach([
                    'customer' => $request->customer_id,
                ]);
            }

            $usuario = $request->user();
            $usuario->stripe_payment_method_id = $request->payment_method;
            $usuario->save();

            // Obtener información de la tarjeta para mostrar al usuario
            $card = $paymentMethod->card;
            
            return response()->json([
                'message' => 'Método de pago guardado correctamente',
                'tarjeta' => [
                    'brand' => $card->brand,
                    'last4' => $card->last4,
                    'exp_month' => $card->exp_month,
                    'exp_year' => $card->exp_year,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al guardar método de pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesar pago domiciliado con tarjeta guardada
     */
    public function pagarDomiciliado(Request $request)
    {
        $request->validate([
            'id_lugar' => 'required|exists:Lugar,id_lugar',
            'id_metodo_pago' => 'required|exists:Metodo_Pago,id_metodo_pago',
            'monto' => 'nullable|numeric|min:1',
        ]);

        try {
            DB::beginTransaction();

            $usuario = auth()->user();
            $lugar = Lugar::findOrFail($request->id_lugar);

            if ($lugar->activo) {
                return response()->json(['message' => 'Este lugar ya ha sido activado previamente.'], 400);
            }

            // Verificar que el usuario tenga tarjeta guardada
            if (!$usuario->stripe_customer_id || !$usuario->stripe_payment_method_id) {
                return response()->json([
                    'message' => 'No tienes una tarjeta guardada para domiciliar. Primero debes guardar una tarjeta.',
                ], 400);
            }

            $monto = $request->filled('monto') ? intval($request->monto * 100) : self::PRECIO_FIJO;

            $metodo = MetodoPago::find($request->id_metodo_pago);
            if (!$metodo) {
                return response()->json(['message' => 'Método de pago no encontrado'], 404);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            // Verificar que el payment method aún existe y está activo
            try {
                $paymentMethod = PaymentMethod::retrieve($usuario->stripe_payment_method_id);
                
                if ($paymentMethod->customer !== $usuario->stripe_customer_id) {
                    return response()->json(['message' => 'Método de pago no válido'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['message' => 'Método de pago no encontrado en Stripe'], 400);
            }

            // Crear Payment Intent para pago domiciliado
            $intent = PaymentIntent::create([
                'amount' => $monto,
                'currency' => 'mxn',
                'customer' => $usuario->stripe_customer_id,
                'payment_method' => $usuario->stripe_payment_method_id,
                'off_session' => true, // Importante: esto indica que es un pago sin el usuario presente
                'confirm' => true,
                'description' => 'Pago domiciliado para lugar: ' . $lugar->nombre,
                'metadata' => [
                    'id_lugar' => $lugar->id_lugar,
                    'id_usuario' => $usuario->id_usuario,
                    'tipo_pago' => 'domiciliado',
                ],
            ]);

            if ($intent->status === 'succeeded') {
                // Crear registro de pago
                $pago = new Pago();
                $pago->id_usuario = $usuario->id_usuario;
                $pago->id_lugar = $lugar->id_lugar;
                $pago->id_metodo_pago = $metodo->id_metodo_pago;
                $pago->monto = $monto / 100;
                $pago->fecha_pago = now();
                $pago->stripe_payment_intent_id = $intent->id; // Guardar referencia de Stripe
                $pago->save();

                // Activar lugar
                $lugar->activo = true;
                $lugar->fecha_activacion = now();
                $lugar->save();

                DB::commit();

                return response()->json([
                    'message' => 'Pago domiciliado exitoso y lugar activado',
                    'lugar' => $lugar,
                    'pago' => $pago,
                    'stripe_intent_id' => $intent->id,
                ], 201);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'El pago no fue completado',
                    'status' => $intent->status,
                ], 400);
            }

        } catch (CardException $e) {
            DB::rollBack();
            
            // Manejar errores específicos de tarjeta
            $errorMessage = 'Error con la tarjeta: ';
            switch ($e->getStripeCode()) {
                case 'card_declined':
                    $errorMessage .= 'La tarjeta fue rechazada. Verifica los fondos o contacta a tu banco.';
                    break;
                case 'expired_card':
                    $errorMessage .= 'La tarjeta ha expirado. Actualiza tu método de pago.';
                    break;
                case 'insufficient_funds':
                    $errorMessage .= 'Fondos insuficientes en la tarjeta.';
                    break;
                case 'authentication_required':
                    $errorMessage .= 'Se requiere autenticación adicional. Usa el pago manual.';
                    break;
                default:
                    $errorMessage .= $e->getMessage();
            }
            
            return response()->json([
                'message' => $errorMessage,
                'type' => 'card_error',
                'code' => $e->getStripeCode(),
            ], 400);
        } catch (RateLimitException | InvalidRequestException | AuthenticationException | ApiConnectionException | ApiErrorException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error con Stripe: ' . $e->getMessage(),
                'type' => class_basename($e),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el pago domiciliado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener información de la tarjeta guardada del usuario
     */
    public function obtenerTarjetaGuardada(Request $request)
    {
        $usuario = $request->user();
        
        if (!$usuario->stripe_payment_method_id) {
            return response()->json(['message' => 'No tienes tarjeta guardada'], 404);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            $paymentMethod = PaymentMethod::retrieve($usuario->stripe_payment_method_id);
            $card = $paymentMethod->card;
            
            return response()->json([
                'tarjeta' => [
                    'brand' => $card->brand,
                    'last4' => $card->last4,
                    'exp_month' => $card->exp_month,
                    'exp_year' => $card->exp_year,
                    'funding' => $card->funding,
                ],
                'tiene_tarjeta' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener información de la tarjeta',
                'tiene_tarjeta' => false,
            ], 500);
        }
    }

    /**
     * Eliminar tarjeta guardada
     */
    public function eliminarTarjetaGuardada(Request $request)
    {
        $usuario = $request->user();
        
        if (!$usuario->stripe_payment_method_id) {
            return response()->json(['message' => 'No tienes tarjeta guardada'], 404);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            $paymentMethod = PaymentMethod::retrieve($usuario->stripe_payment_method_id);
            $paymentMethod->detach();
            
            // Limpiar referencias en la base de datos
            $usuario->stripe_payment_method_id = null;
            $usuario->save();
            
            return response()->json(['message' => 'Tarjeta eliminada correctamente']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la tarjeta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesar el pago manual (con CVV cada vez)
     */
    public function pagar(Request $request)
    {
        $request->validate([
            'id_lugar' => 'required|exists:Lugar,id_lugar',
            'id_metodo_pago' => 'required|exists:Metodo_Pago,id_metodo_pago',
            'stripeToken' => 'nullable|string',
            'monto' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            $usuario = auth()->user();
            $lugar = Lugar::findOrFail($request->id_lugar);

            if ($lugar->activo) {
                return response()->json(['message' => 'Este lugar ya ha sido activado previamente.'], 400);
            }

            $monto = $request->filled('monto') ? intval($request->monto * 100) : self::PRECIO_FIJO;

            $metodo = MetodoPago::find($request->id_metodo_pago);
            if (!$metodo) {
                return response()->json(['message' => 'Método de pago no encontrado'], 404);
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $pagoExitoso = false;
            $stripeId = null;

            if ($usuario->stripe_payment_method_id && $usuario->stripe_customer_id && !$request->filled('stripeToken')) {
                // Pago con tarjeta guardada (requiere CVV en frontend)
                $intent = PaymentIntent::create([
                    'amount' => $monto,
                    'currency' => 'mxn',
                    'customer' => $usuario->stripe_customer_id,
                    'payment_method' => $usuario->stripe_payment_method_id,
                    'off_session' => false, // Requiere confirmación con CVV
                    'confirm' => true,
                    'description' => 'Pago para lugar: ' . $lugar->nombre,
                    'metadata' => [
                        'id_lugar' => $lugar->id_lugar,
                        'id_usuario' => $usuario->id_usuario,
                        'tipo_pago' => 'manual',
                    ],
                ]);

                $pagoExitoso = $intent->status === 'succeeded';
                $stripeId = $intent->id;
            } elseif ($request->filled('stripeToken')) {
                // Pago con tarjeta nueva
                $charge = Charge::create([
                    'amount' => $monto,
                    'currency' => 'mxn',
                    'description' => 'Pago para lugar: ' . $lugar->nombre,
                    'source' => $request->stripeToken,
                    'metadata' => [
                        'id_lugar' => $lugar->id_lugar,
                        'id_usuario' => $usuario->id_usuario,
                        'tipo_pago' => 'manual',
                    ],
                ]);

                $pagoExitoso = $charge->status === 'succeeded';
                $stripeId = $charge->id;
            } else {
                return response()->json(['message' => 'No se proporcionó stripeToken ni método guardado.'], 422);
            }

            if ($pagoExitoso) {
                $pago = new Pago();
                $pago->id_usuario = $usuario->id_usuario;
                $pago->id_lugar = $lugar->id_lugar;
                $pago->id_metodo_pago = $metodo->id_metodo_pago;
                $pago->monto = $monto / 100;
                $pago->fecha_pago = now();
                $pago->stripe_payment_intent_id = $stripeId;
                $pago->save();

                $lugar->activo = true;
                $lugar->fecha_activacion = now();
                $lugar->save();

                DB::commit();

                return response()->json([
                    'message' => 'Pago exitoso y lugar activado',
                    'lugar' => $lugar,
                    'pago' => $pago,
                ], 201);
            } else {
                DB::rollBack();
                return response()->json(['message' => 'El pago no fue exitoso'], 400);
            }
        } catch (CardException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error con la tarjeta: ' . $e->getMessage(),
                'type' => 'card_error',
                'code' => $e->getStripeCode(),
            ], 400);
        } catch (RateLimitException | InvalidRequestException | AuthenticationException | ApiConnectionException | ApiErrorException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error con Stripe: ' . $e->getMessage(),
                'type' => class_basename($e),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener todos los pagos (sólo anunciantes)
     */
    public function index()
    {
        if (auth()->user()->rol->nombre !== 'Anunciante') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $pagos = Pago::with(['usuario', 'lugar', 'metodoPago'])->get();
        return response()->json($pagos);
    }

    /**
     * Mostrar un pago específico
     */
    public function show($id)
    {
        $pago = Pago::findOrFail($id);

        if (auth()->user()->rol->nombre !== 'Anunciante' && $pago->id_usuario !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($pago);
    }

    /**
     * Eliminar un pago
     */
    public function destroy($id)
    {
        if (auth()->user()->rol->nombre !== 'Anunciante') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $pago = Pago::findOrFail($id);
        $pago->delete();

        return response()->json(['message' => 'Pago eliminado correctamente']);
    }

    /**
     * Obtener pagos del usuario autenticado
     */
    public function misPagos()
    {
        $pagos = Pago::with(['lugar', 'metodoPago'])
            ->where('id_usuario', auth()->id())
            ->get();

        return response()->json($pagos);
    }
    /**
     * Verificar estado de un pago en Stripe
     */
    public function verificarPago($payment_intent_id)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            
            $intent = PaymentIntent::retrieve($payment_intent_id);
            
            // Buscar pago en BD
            $pago = Pago::where('stripe_payment_intent_id', $payment_intent_id)->first();
            
            if (!$pago) {
                return response()->json(['message' => 'Pago no encontrado en la base de datos'], 404);
            }
            
            // Verificar autorización
            if (auth()->user()->rol->nombre !== 'Anunciante' && $pago->id_usuario !== auth()->id()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }
            
            // Actualizar estado si es necesario
            if ($pago->stripe_status !== $intent->status) {
                $pago->stripe_status = $intent->status;
                $pago->save();
            }
            
            return response()->json([
                'pago' => $pago,
                'stripe_status' => $intent->status,
                'stripe_amount' => $intent->amount,
                'stripe_currency' => $intent->currency,
                'created' => date('Y-m-d H:i:s', $intent->created),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al verificar el pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    

    /**
     * Reembolsar un pago
     */
    public function reembolsar(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
            'monto' => 'nullable|numeric|min:0',
        ]);

        // Solo anunciantes pueden hacer reembolsos
        if (auth()->user()->rol->nombre !== 'Anunciante') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        try {
            DB::beginTransaction();
            
            $pago = Pago::findOrFail($id);
            
            if (!$pago->stripe_payment_intent_id) {
                return response()->json(['message' => 'Pago no procesado con Stripe'], 400);
            }
            
            Stripe::setApiKey(config('services.stripe.secret'));
            
            // Monto del reembolso (si no se especifica, reembolsar todo)
            $montoReembolso = $request->filled('monto') 
                ? intval($request->monto * 100) 
                : intval($pago->monto * 100);
            
            // Crear reembolso en Stripe
            $refund = \Stripe\Refund::create([
                'payment_intent' => $pago->stripe_payment_intent_id,
                'amount' => $montoReembolso,
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'motivo' => $request->motivo,
                    'admin_id' => auth()->id(),
                ],
            ]);
            
            // Actualizar pago en BD
            $pago->stripe_status = 'refunded';
            $pago->save();
            
            // Desactivar lugar si es reembolso total
            if ($montoReembolso == intval($pago->monto * 100)) {
                $lugar = Lugar::find($pago->id_lugar);
                if ($lugar) {
                    $lugar->activo = false;
                    $lugar->save();
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Reembolso procesado exitosamente',
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el reembolso',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}