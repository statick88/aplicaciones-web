<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Categoria — entidad 2 de 5 de la consigna (Semana 4 - Tarea 1).
 * Relación 1:N con libros.
 */
class Categoria extends Model
{
    public $timestamps = false;

    /** @use HasFactory<\Database\Factories\CategoriaFactory> */
    use HasFactory;

    /** Los 5 atributos de negocio de la consigna (el id es adicional). */
    protected $fillable = [
        'nombre',
        'descripcion',
        'color',
        'orden',
        'activa',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'activa' => 'boolean',
        ];
    }

    /** Una categoría agrupa muchos libros. */
    public function libros(): HasMany
    {
        return $this->hasMany(Libro::class);
    }
}
