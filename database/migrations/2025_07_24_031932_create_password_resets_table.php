<?php
// ===== MIGRACIÓN PARA PASSWORD RESETS =====
// database/migrations/xxxx_xx_xx_create_password_resets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsTable extends Migration
{
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('correo')->index(); // Usar 'correo' en lugar de 'email'
            $table->string('token');
            $table->timestamp('created_at');
            $table->timestamp('expires_at');
            $table->integer('attempts')->default(0);
            
            // Clave foránea opcional (si quieres relacionar directamente)
            // $table->foreign('correo')->references('correo')->on('Usuario')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}