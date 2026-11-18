<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Prestamo — entidad 5 de 5 de la consigna (Semana 4 - Tarea 1).
 *
 * Conecta a un lector con un libro: es la entidad que hace que las
 * cinco entidades queden relacionadas entre sí en un solo grafo.
 */
class Prestamo extends Model
{
    public $timestamps = false;

    /** @use HasFactory<\Database\Factories\PrestamoFactory> */
    use HasFactory;

    /** Los 5 atributos de negocio de la consigna (el id es adicional). */
    protected $fillable = [
        'fecha_prestamo',
        'fecha_devolucion',
        'estado',
        'lector_id',
        'libro_id',
    ];

    /** Estados posibles del préstamo (se validan también en el request). */
    public const ESTADOS = ['activo', 'devuelto', 'atrasado'];

    protected function casts(): array
    {
        return [
            'fecha_prestamo' => 'date',
            'fecha_devolucion' => 'date',
            'lector_id' => 'integer',
            'libro_id' => 'integer',
        ];
    }

    /** Un préstamo pertenece a un lector. */
    public function lector(): BelongsTo
    {
        return $this->belongsTo(Lector::class);
    }

    /** Un préstamo corresponde a un libro. */
    public function libro(): BelongsTo
    {
        return $this->belongsTo(Libro::class);
    }
}
