<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MetodoPago extends Model
{
    protected $table = 'metodo_pago';

    protected $fillable = ['nombre'];

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'metodo_pago_id');
    }
}
