<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    
    protected $fillable = [
        'correo', // Cambiar de 'email' a 'correo'
        'token', 
        'created_at', 
        'expires_at',
        'attempts'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public $timestamps = false;

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'correo', 'correo');
    }

    // Verificar si el token ha expirado
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    // Verificar si se han superado los intentos máximos
    public function hasExceededAttempts()
    {
        return $this->attempts >= 5;
    }

    // Incrementar intentos
    public function incrementAttempts()
    {
        $this->increment('attempts');
    }
}    