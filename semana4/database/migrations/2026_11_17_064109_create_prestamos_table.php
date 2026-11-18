<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entidad 5 de 5: prestamos (id + 5 atributos de negocio).
     *
     * Es la entidad de unión entre lectores y libros: sus dos últimas
     * columnas son las claves foráneas de las relaciones 1:N con
     * lectores y con libros.
     *
     * `estado` ∈ activo | devuelto | atrasado (se valida también en el
     * FormRequest de la capa web).
     */
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_prestamo');
            $table->date('fecha_devolucion')->nullable();
            $table->string('estado', 20)->default('activo');
            $table->foreignId('lector_id')->constrained('lectores')->restrictOnDelete();
            $table->foreignId('libro_id')->constrained('libros')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
