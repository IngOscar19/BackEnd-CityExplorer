<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PipedreamService
{
    protected $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = 'https://eo4si0ii23bf0w1.m.pipedream.net';
    
        // DEBUG TEMPORAL - CORREGIDO: quitar la barra invertida
        Log::info('=== PIPEDREAM CONFIG DEBUG ===', [
            'webhook_url' => $this->webhookUrl,
            'env_exists' => env('PIPEDREAM_WEBHOOK_URL') ? 'SÍ' : 'NO',
            'is_null' => is_null($this->webhookUrl) ? 'SÍ' : 'NO'
        ]);
    }

    public function sendPasswordResetEmail(string $correo, string $nombreCompleto, string $codigo): void
    {
        try {
            // Log para debugging
            Log::info('Enviando email via Pipedream', [
                'correo' => $correo,
                'nombre_completo' => $nombreCompleto,
                'code' => $codigo,
                'webhook_url' => $this->webhookUrl
            ]);

            // CORREGIDO: Usar los nombres correctos que espera Pipedream
            $response = Http::post($this->webhookUrl, [
                'correo' => $correo,
                'nombre_completo' => $nombreCompleto,  // ✅ Cambiado de 'nombre' a 'nombre_completo'
                'code' => $codigo,                     // ✅ Cambiado de 'codigo' a 'code'
            ]);

            // Log de la respuesta
            if ($response->successful()) {
                Log::info('Email enviado exitosamente via Pipedream', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
            } else {
                Log::error('Error al enviar email via Pipedream', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'correo' => $correo
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Excepción al enviar email via Pipedream', [
                'error' => $e->getMessage(),
                'correo' => $correo,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-lanzar la excepción para que la maneje el controlador
            throw $e;
        }
    }
}