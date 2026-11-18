<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Fija el requisito duro de la consigna de la Semana 4 - Tarea 1:
 *
 *   "la base de datos debe tener un máximo de 5 entidades y cada
 *    entidad con 5 atributos"
 *
 * Se usa el criterio decidido por el usuario: el `id` es adicional,
 * así que cada entidad queda con id + 5 atributos de negocio
 * (6 columnas), igual que en la Semana 3 con los 12 campos.
 */
class DatabaseStructureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Las 5 entidades de la consigna y sus 5 atributos de negocio,
     * en el orden exacto en que quedan en la tabla.
     */
    private const ENTIDADES = [
        'editoriales' => ['nombre', 'pais', 'pagina_web', 'fundacion', 'activa'],
        'categorias' => ['nombre', 'descripcion', 'color', 'orden', 'activa'],
        'libros' => ['titulo', 'anio', 'isbn', 'editorial_id', 'categoria_id'],
        'lectores' => ['nombre', 'cedula', 'correo', 'telefono', 'fecha_registro'],
        'prestamos' => ['fecha_prestamo', 'fecha_devolucion', 'estado', 'lector_id', 'libro_id'],
    ];

    /**
     * Tablas que crea el propio Laravel (sesiones, caché, usuarios del
     * framework...) y que no son entidades del negocio: no cuentan para
     * el "máximo de 5 entidades".
     */
    private const TABLAS_FRAMEWORK = [
        'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs',
        'migrations', 'password_reset_tokens', 'sessions', 'users',
    ];

    public function test_existen_exactamente_las_cinco_entidades_de_la_consigna(): void
    {
        // `Schema::getTables()` lista todos los esquemas visibles para el
        // usuario de MySQL (semana4 y semana4_test): nos quedamos con el
        // esquema al que está conectada la aplicación.
        $esquema = config('database.connections.mysql.database');
        $nombres = array_column(
            array_filter(
                Schema::getTables(),
                fn (array $tabla) => $tabla['schema'] === $esquema
            ),
            'name'
        );

        $dominio = array_values(array_diff($nombres, self::TABLAS_FRAMEWORK));
        sort($dominio);

        $esperadas = array_keys(self::ENTIDADES);
        sort($esperadas);

        $this->assertSame(
            $esperadas,
            $dominio,
            'La base debe tener máximo 5 entidades y exactamente las de la consigna.'
        );
    }

    public function test_cada_entidad_tiene_id_mas_cinco_atributos(): void
    {
        foreach (self::ENTIDADES as $tabla => $atributos) {
            $columnas = array_column(Schema::getColumns($tabla), 'name');

            $this->assertSame(
                array_merge(['id'], $atributos),
                $columnas,
                "Las columnas de `$tabla` deben ser id + sus 5 atributos de negocio."
            );
        }
    }

    public function test_las_claves_foraneas_estan_definidas_en_la_base(): void
    {
        $relaciones = $this->clavesForaneas();
        ksort($relaciones);

        $this->assertSame(
            [
                'libros.categoria_id' => 'categorias',
                'libros.editorial_id' => 'editoriales',
                'prestamos.lector_id' => 'lectores',
                'prestamos.libro_id' => 'libros',
            ],
            $relaciones,
            'Cada relación entre entidades debe existir como clave foránea en MySQL.'
        );
    }

    /**
     * @return array<string, string> columna → tabla referenciada
     */
    private function clavesForaneas(): array
    {
        $filas = \DB::select(
            'SELECT CONCAT(TABLE_NAME, \'.\', COLUMN_NAME) AS columna, REFERENCED_TABLE_NAME AS referenciada
               FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME IS NOT NULL
                AND TABLE_NAME IN (?, ?, ?, ?, ?)',
            array_keys(self::ENTIDADES)
        );

        $resultado = [];
        foreach ($filas as $fila) {
            $resultado[$fila->columna] = $fila->referenciada;
        }
        ksort($resultado);

        return $resultado;
    }
}
