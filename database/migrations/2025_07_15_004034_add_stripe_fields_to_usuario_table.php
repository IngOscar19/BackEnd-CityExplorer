<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeFieldsToUsuarioTable extends Migration
{
    public function up()
    {
        Schema::table('Usuario', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('correo');
            $table->string('stripe_payment_method_id')->nullable()->after('stripe_customer_id');
        });
    }

    public function down()
    {
        Schema::table('Usuario', function (Blueprint $table) {
            $table->dropColumn(['stripe_customer_id', 'stripe_payment_method_id']);
        });
    }
}

