<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Editorial — entidad 1 de 5 de la consigna (Semana 4 - Tarea 1).
 *
 * Relación 1:N con libros: una editorial publica muchos libros y un
 * libro pertenece a una sola editorial.
 */
class Editorial extends Model
{
    public $timestamps = false;

    /** @use HasFactory<\Database\Factories\EditorialFactory> */
    use HasFactory;

    /**
     * El plural en español de "editorial" es "editoriales"; Eloquent
     * habría inferido "editorials", así que la tabla se declara a mano.
     */
    protected $table = 'editoriales';

    /** Los 5 atributos de negocio de la consigna (el id es adicional). */
    protected $fillable = [
        'nombre',
        'pais',
        'pagina_web',
        'fundacion',
        'activa',
    ];

    /**
     * Tipos explícitos: sin esto `activa` llegaría como string "0"/"1"
     * desde MySQL y las comparaciones booleanas fallarían.
     */
    protected function casts(): array
    {
        return [
            'fundacion' => 'integer',
            'activa' => 'boolean',
        ];
    }

    /** Una editorial tiene muchos libros. */
    public function libros(): HasMany
    {
        return $this->hasMany(Libro::class);
    }
}
