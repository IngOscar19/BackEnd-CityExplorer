<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imagenes extends Model
{
    use HasFactory;

    protected $table = 'Imagenes';
    protected $primaryKey = 'id_imagen';
    public $timestamps = false;

    protected $fillable = [
        'id_lugar',
        'url'
    ];

    public function lugar()
    {
        return $this->belongsTo(Lugar::class, 'id_lugar');
    }
}
