<?php

// Migración 1: Agregar campos a la tabla Pago
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeFieldsToPagoTable extends Migration
{
    public function up()
    {
        Schema::table('Pago', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id')->nullable()->after('fecha_pago');
            $table->string('tipo_pago')->default('manual')->after('stripe_payment_intent_id'); // 'manual' o 'domiciliado'
            $table->string('stripe_status')->nullable()->after('tipo_pago'); // 'succeeded', 'failed', etc.
        });
    }

    public function down()
    {
        Schema::table('Pago', function (Blueprint $table) {
            $table->dropColumn(['stripe_payment_intent_id', 'tipo_pago', 'stripe_status']);
        });
    }
}