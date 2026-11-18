<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Libro — entidad 3 de 5 de la consigna (Semana 4 - Tarea 1).
 *
 * Es el centro del modelo: se relaciona con editoriales y categorias
 * (N:1) y con prestamos (1:N).
 */
class Libro extends Model
{
    public $timestamps = false;

    /** @use HasFactory<\Database\Factories\LibroFactory> */
    use HasFactory;

    /** Los 5 atributos de negocio de la consigna (el id es adicional). */
    protected $fillable = [
        'titulo',
        'anio',
        'isbn',
        'editorial_id',
        'categoria_id',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'editorial_id' => 'integer',
            'categoria_id' => 'integer',
        ];
    }

    /** Un libro pertenece a una editorial. */
    public function editorial(): BelongsTo
    {
        return $this->belongsTo(Editorial::class);
    }

    /** Un libro pertenece a una categoría. */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /** Un libro puede estar prestado varias veces. */
    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class);
    }
}
