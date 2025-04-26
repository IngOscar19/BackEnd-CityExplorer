<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lugar;
use App\Models\Pago;
use App\Models\MetodoPago;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;


class PagoController extends Controller
{
   // Precio fijo para todos los lugares (en centavos)
   const PRECIO_FIJO = 10000; // Equivale a $100.00 MXN


   /**
    * Procesar el pago y activar el lugar
    */
   public function pagar(Request $request)
   {




       $request->validate([
           'id_lugar' => 'required|exists:Lugar,id_lugar',
           'id_metodo_pago' => 'required|exists:Metodo_Pago,id_metodo_pago',
           'stripeToken' => 'required|string',
          
       ]);


       try {
           DB::beginTransaction();


           $lugar = Lugar::findOrFail($request->id_lugar);


           // Verificamos si el lugar ya está activo
           if ($lugar->activo === true) {
               return response()->json([
                   'message' => 'Este lugar ya ha sido activado previamente.',
               ], 400);
           }


           // Determinar el monto del pago
           $monto = $request->filled('monto') ?
                    intval($request->monto * 100) :
                    self::PRECIO_FIJO;


           $metodo = DB::table('Metodo_Pago')->where('id_metodo_pago', $request->id_metodo_pago)->first();
               if (!$metodo) {
                     return response()->json(['message' => 'Método de pago no encontrado'], 404);
                   
                  }
                      


           // Verificar que se ha configurado la clave de API de Stripe
           $stripeKey = config('services.stripe.secret');
           if (!$stripeKey) {
               return response()->json([
                   'message' => 'Error de configuración: Stripe no está configurado correctamente.',
               ], 500);
           }


           // Configurar Stripe
           Stripe::setApiKey($stripeKey);


           // Crear el PaymentIntent
           $charge = Charge::create([
               'amount' => $monto,
               'currency' => 'mxn',
               'description' => 'Pago para lugar: ' . $lugar->nombre,
               'source' => $request->stripeToken,
               'metadata' => [
                   'id_lugar' => $lugar->id_lugar,
                   'nombre_lugar' => $lugar->nombre,
                   'id_usuario' => auth()->id()
               ]
           ]);
          


           if ($charge->status === 'succeeded') {
               // Crear el registro de pago
               $pago = new Pago();
               $pago->id_usuario = auth()->id();
               $pago->id_lugar = $lugar->id_lugar;
               $pago->id_metodo_pago = $metodo->id_metodo_pago;
               $pago->monto = $monto / 100; // Convertir a unidades monetarias
               $pago->fecha_pago = now();
               $pago->save();


               // Activar el lugar
               $lugar->activo = true;
               $lugar->save();


               DB::commit();


               return response()->json([
                   'message' => 'Pago exitoso y lugar activado',
                   'lugar' => $lugar,
                   'pago' => $pago,
               ], 201);
           } else {
               DB::rollBack();
               return response()->json([
                   'message' => 'El pago no fue exitoso',
                   'stripe_status' => $intent->status,
               ], 400);
           }


       } catch (CardException $e) {
           DB::rollBack();
           return response()->json([
               'message' => 'Error con la tarjeta: ' . $e->getMessage(),
               'type' => 'card_error',
               'code' => $e->getStripeCode(),
           ], 400);
       } catch (RateLimitException $e) {
           DB::rollBack();
           return response()->json([
               'message' => 'Demasiadas solicitudes a Stripe. Intente más tarde.',
               'type' => 'rate_limit_error',
           ], 429);
       } catch (InvalidRequestException $e) {
           DB::rollBack();
           return response()->json([
               'message' => 'Parámetros inválidos en la solicitud a Stripe: ' . $e->getMessage(),
               'type' => 'invalid_request_error',
           ], 400);
       } catch (AuthenticationException $e) {
           DB::rollBack();
           return response()->json([
               'message' => 'Error de autenticación con Stripe. Verifique las credenciales API.',
               'type' => 'authentication_error',
           ], 500);
       } catch (ApiConnectionException $e) {
           DB::rollBack();
           return response()->json([
               'message' => 'Error de conexión con la API de Stripe.',
               'type' => 'api_connection_error',
           ], 500);
       } catch (ApiErrorException $e) {
           DB::rollBack();
           return response()->json([
               'message' => 'Error general de la API de Stripe: ' . $e->getMessage(),
               'type' => 'api_error',
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
    * Obtener listado de pagos (solo para administradores)
    */
   public function index()
   {
       // Verificar si el usuario es administrador
       if (auth()->user()->rol->nombre !== 'Anunciante') {
           return response()->json(['message' => 'No autorizado'], 403);
       }


       $pagos = Pago::with(['usuario', 'lugar', 'metodoPago'])->get();
       return response()->json($pagos);
   }


   /**
    * Obtener un pago específico
    */
   public function show($id)
   {
       $pago = Pago::findOrFail($id);
      
       // Verificar si el usuario es administrador o el dueño del pago
       if (auth()->user()->rol->nombre !== 'Anunciante' && $pago->id_usuario !== auth()->id()) {
           return response()->json(['message' => 'No autorizado'], 403);
       }


       return response()->json($pago);
   }


   /**
    * Eliminar un pago (solo administradores)
    */
   public function destroy($id)
   {
       // Verificar si el usuario es administrador
       if (auth()->user()->rol->nombre !== 'Anunciante') {
           return response()->json(['message' => 'No autorizado'], 403);
       }


       $pago = Pago::findOrFail($id);
       $pago->delete();


       return response()->json(['message' => 'Pago eliminado correctamente']);
   }


   /**
    * Obtener los pagos del usuario autenticado
    */
   public function misPagos()
   {
       $pagos = Pago::with(['lugar', 'metodoPago'])
           ->where('id_usuario', auth()->id())
           ->get();


       return response()->json($pagos);
   }
}
