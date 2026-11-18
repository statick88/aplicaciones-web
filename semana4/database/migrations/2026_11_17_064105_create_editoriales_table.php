<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entidad 1 de 5: editoriales.
     *
     * Consigna de la Semana 4 - Tarea 1: "un máximo de 5 entidades y
     * cada entidad con 5 atributos". Aquí el `id` es adicional (criterio
     * decidido por el usuario, igual que los 12 campos de la Semana 3),
     * así que la tabla queda con id + 5 atributos de negocio.
     */
    public function up(): void
    {
        Schema::create('editoriales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('pais', 60);
            $table->string('pagina_web', 120)->nullable();
            $table->unsignedSmallInteger('fundacion')->nullable();
            $table->boolean('activa')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editoriales');
    }
};
