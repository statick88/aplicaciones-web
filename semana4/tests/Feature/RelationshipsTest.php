<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Editorial;
use App\Models\Lector;
use App\Models\Libro;
use App\Models\Prestamo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Comprueba el otro requisito duro de la consigna:
 *
 *   "Deben tener en cuenta que las entidades deben estar
 *    relacionadas entre si."
 *
 * Relaciones 1:N: editoriales→libros, categorias→libros,
 * lectores→prestamos y libros→prestamos, verificadas en ambos
 * sentidos y con la restricción de integridad de MySQL.
 */
class RelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_editorial_tiene_sus_libros_y_el_libro_conoce_su_editorial(): void
    {
        $editorial = Editorial::factory()->create();
        $libro = Libro::factory()->for($editorial)->create();

        $this->assertCount(1, $editorial->libros);
        $this->assertTrue($editorial->libros->contains($libro));
        $this->assertTrue($libro->editorial->is($editorial));
    }

    public function test_una_categoria_agrupa_sus_libros(): void
    {
        $categoria = Categoria::factory()->create();
        $libro = Libro::factory()->for($categoria)->create();

        $this->assertCount(1, $categoria->libros);
        $this->assertTrue($libro->categoria->is($categoria));
    }

    public function test_un_lector_tiene_sus_prestamos_y_el_prestamo_conoce_su_lector(): void
    {
        $lector = Lector::factory()->create();
        $prestamo = Prestamo::factory()->for($lector)->create();

        $this->assertCount(1, $lector->prestamos);
        $this->assertTrue($prestamo->lector->is($lector));
    }

    public function test_un_libro_tiene_sus_prestamos(): void
    {
        $libro = Libro::factory()->create();
        $prestamo = Prestamo::factory()->for($libro)->create();

        $this->assertCount(1, $libro->prestamos);
        $this->assertTrue($prestamo->libro->is($libro));
    }

    public function test_no_se_puede_eliminar_una_entidad_referenciada(): void
    {
        $editorial = Editorial::factory()->create();
        Libro::factory()->for($editorial)->create();

        $this->expectException(\Illuminate\Database\QueryException::class);

        $editorial->delete();
    }

    public function test_eliminar_una_entidad_sin_referencias_si_funciona(): void
    {
        $categoria = Categoria::factory()->create();

        $this->assertTrue($categoria->delete());
        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
        $this->assertSame(0, DB::table('categorias')->count());
    }
}
