<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entidad 4 de 5: lectores (id + 5 atributos de negocio).
     * `cedula` y `correo` son únicos: una persona no se registra dos
     * veces (validación doble: en la aplicación y en la base).
     */
    public function up(): void
    {
        Schema::create('lectores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->char('cedula', 10)->unique();
            $table->string('correo', 160)->unique();
            $table->string('telefono', 20);
            $table->date('fecha_registro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lectores');
    }
};
