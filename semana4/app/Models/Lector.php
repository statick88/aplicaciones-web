<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lector — entidad 4 de 5 de la consigna (Semana 4 - Tarea 1).
 * Relación 1:N con prestamos.
 */
class Lector extends Model
{
    public $timestamps = false;

    /** @use HasFactory<\Database\Factories\LectorFactory> */
    use HasFactory;

    /** "lector" en plural es "lectores"; Eloquent infería "lectors". */
    protected $table = 'lectores';

    /** Los 5 atributos de negocio de la consigna (el id es adicional). */
    protected $fillable = [
        'nombre',
        'cedula',
        'correo',
        'telefono',
        'fecha_registro',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'date',
            'lector_id' => 'integer',
        ];
    }

    /** Un lector tiene muchos préstamos. */
    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class);
    }
}
