<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entidad 2 de 5: categorias (id + 5 atributos de negocio).
     * `color` guarda el valor #RRGGBB que pinta la interfaz.
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('descripcion', 255)->nullable();
            $table->char('color', 7);
            $table->unsignedTinyInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
