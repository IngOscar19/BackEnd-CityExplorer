<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comentario extends Model
{
    use HasFactory;

    protected $table = 'Comentario'; 

    protected $primaryKey = 'id_comentario'; 

    public $timestamps = false; // Laravel no usará created_at ni updated_at automáticamente

    protected $fillable = [
        'contenido',
        'valoracion',
        'fecha_creacion',
        'id_usuario',
        'id_lugar',
    ];

    protected $casts = [
        'valoracion' => 'boolean',
        'fecha_creacion' => 'datetime',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function lugar()
    {
        return $this->belongsTo(Lugar::class, 'id_lugar');
    }
}
