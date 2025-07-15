<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActivationFieldsToLugarTable extends Migration
{
    public function up()
    {
        Schema::table('Lugar', function (Blueprint $table) {
            $table->timestamp('fecha_activacion')->nullable()->after('activo');
            $table->string('activado_por_pago_id')->nullable()->after('fecha_activacion'); // referencia al pago que lo activó
        });
    }

    public function down()
    {
        Schema::table('Lugar', function (Blueprint $table) {
            $table->dropColumn(['fecha_activacion', 'activado_por_pago_id']);
        });
    }
}
