<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entidad 3 de 5: libros (id + 5 atributos de negocio).
     *
     * Dos de esos 5 atributos son las claves foráneas que relacionan
     * esta entidad con editoriales y categorias; por eso se declaran
     * al final para que el orden de columnas sea exactamente el del
     * contrato de la consigna.
     *
     * `restrictOnDelete()`: no se puede borrar una editorial o una
     * categoría que todavía tenga libros (integridad referencial real
     * en MySQL, no solo validación de la aplicación).
     */
    public function up(): void
    {
        Schema::create('libros', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->unsignedSmallInteger('anio');
            $table->char('isbn', 13)->unique();
            $table->foreignId('editorial_id')->constrained('editoriales')->restrictOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libros');
    }
};
