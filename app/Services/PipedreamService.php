<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PipedreamService
{
    protected $webhookUrl;

    public function __construct()
    {
        // Usa una variable de entorno para el webhook
        $this->webhookUrl = env('PIPEDREAM_WEBHOOK_URL');
    }

    public function sendPasswordResetEmail(string $correo, string $nombreCompleto, string $codigo): void
    {
        Http::post($this->webhookUrl, [
            'correo' => $correo,
            'nombre' => $nombreCompleto,
            'codigo' => $codigo,
        ]);
    }
}