<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Editorial;
use App\Models\Lector;
use App\Models\Libro;
use App\Models\Prestamo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudHttpTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Definición por entidad: ruta, tabla, modelo y payloads.
     *
     * @return array<string, array{0: string, 1: string, 2: class-string, 3: array<string, mixed>, 4: array<string, mixed>}>
     */
    public static function entidades(): array
    {
        $editorial = Editorial::factory()->create();
        $categoria = Categoria::factory()->create();
        $lector = Lector::factory()->create();
        $libro = Libro::factory()->create();

        return [
            'editoriales' => [
                'editoriales',
                'editoriales',
                Editorial::class,
                ['nombre' => 'Planeta', 'pais' => 'España', 'pagina_web' => 'https://planeta.es', 'fundacion' => 1949, 'activa' => true],
                ['nombre' => '', 'pais' => ''],
            ],
            'categorias' => [
                'categorias',
                'categorias',
                Categoria::class,
                ['nombre' => 'Novela', 'descripcion' => 'Ficción', 'color' => '#FF5733', 'orden' => 1, 'activa' => true],
                ['nombre' => '', 'color' => 'no-es-color'],
            ],
            'libros' => [
                'libros',
                'libros',
                Libro::class,
                ['titulo' => 'El principito', 'anio' => 1943, 'isbn' => '9788437604947', 'editorial_id' => $editorial->id, 'categoria_id' => $categoria->id],
                ['titulo' => '', 'isbn' => '123'],
            ],
            'lectores' => [
                'lectores',
                'lectores',
                Lector::class,
                ['nombre' => 'Ana Pérez', 'cedula' => '1712345678', 'correo' => 'ana@correo.com', 'telefono' => '0981234567', 'fecha_registro' => '2026-01-15'],
                ['nombre' => '', 'cedula' => '123', 'correo' => 'no-es-correo'],
            ],
            'prestamos' => [
                'prestamos',
                'prestamos',
                Prestamo::class,
                ['fecha_prestamo' => '2026-01-10', 'fecha_devolucion' => null, 'estado' => 'activo', 'lector_id' => $lector->id, 'libro_id' => $libro->id],
                ['fecha_prestamo' => '', 'estado' => 'inventado'],
            ],
        ];
    }

    public function test_el_listado_de_cada_entidad_responde_200(): void
    {
        foreach (self::entidades() as [$ruta, , , ]) {
            $this->get("/{$ruta}")->assertOk();
        }
    }

    public function test_el_listado_muestra_los_registros_creados(): void
    {
        $editorial = Editorial::factory()->create(['nombre' => 'Editorial Visible']);

        $this->get('/editoriales')
            ->assertOk()
            ->assertSee('Editorial Visible');
    }

    public function test_el_formulario_de_creacion_responde_200(): void
    {
        foreach (self::entidades() as [$ruta, , , ]) {
            $this->get("/{$ruta}/create")->assertOk();
        }
    }

    public function test_store_crea_el_registro_con_datos_validos(): void
    {
        foreach (self::entidades() as [$ruta, $tabla, $modelo, $payload, ]) {
            $antes = $modelo::count();

            $this->post("/{$ruta}", $payload)
                ->assertRedirect("/{$ruta}");

            $this->assertSame($antes + 1, $modelo::count(), "`{$ruta}` debe crear un registro.");
            $this->assertDatabaseHas($tabla, ['id' => $modelo::latest('id')->value('id')]);
        }
    }

    public function test_store_rechaza_datos_invalidos_y_no_crea_el_registro(): void
    {
        foreach (self::entidades() as [$ruta, , $modelo, , $invalido]) {
            $antes = $modelo::count();

            $this->from("/{$ruta}/create")
                ->post("/{$ruta}", $invalido)
                ->assertRedirect("/{$ruta}/create")
                ->assertSessionHasErrors();

            $this->assertSame($antes, $modelo::count(), "`{$ruta}` no debe crear registros inválidos.");
        }
    }

    public function test_show_muestra_un_registro(): void
    {
        $lector = Lector::factory()->create(['nombre' => 'Lector Destacado']);

        $this->get("/lectores/{$lector->id}")
            ->assertOk()
            ->assertSee('Lector Destacado');
    }

    public function test_edit_muestra_el_formulario_con_los_datos(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Ciencia']);

        $this->get("/categorias/{$categoria->id}/edit")
            ->assertOk()
            ->assertSee('Ciencia');
    }

    public function test_update_modifica_el_registro(): void
    {
        $libro = Libro::factory()->create(['titulo' => 'Título original']);

        $this->put("/libros/{$libro->id}", [
            'titulo' => 'Título actualizado',
            'anio' => $libro->anio,
            'isbn' => $libro->isbn,
            'editorial_id' => $libro->editorial_id,
            'categoria_id' => $libro->categoria_id,
        ])->assertRedirect("/libros/{$libro->id}");

        $this->assertDatabaseHas('libros', ['id' => $libro->id, 'titulo' => 'Título actualizado']);
    }

    public function test_update_rechaza_datos_invalidos(): void
    {
        $lector = Lector::factory()->create();

        $this->from("/lectores/{$lector->id}/edit")
            ->put("/lectores/{$lector->id}", [
                'nombre' => '',
                'cedula' => 'abc',
                'correo' => 'no-es-correo',
                'telefono' => $lector->telefono,
                'fecha_registro' => $lector->fecha_registro,
            ])
            ->assertRedirect("/lectores/{$lector->id}/edit")
            ->assertSessionHasErrors(['nombre', 'cedula', 'correo']);

        $this->assertDatabaseMissing('lectores', ['cedula' => 'abc']);
    }

    public function test_formulario_activa_incluye_fallback_oculto(): void
    {
        $categoria = Categoria::factory()->create(['activa' => true]);
        $editorial = Editorial::factory()->create(['activa' => true]);

        $this->get("/categorias/{$categoria->id}/edit")
            ->assertOk()
            ->assertSee('<input type="hidden" name="activa" value="0">', false);

        $this->get("/editoriales/{$editorial->id}/edit")
            ->assertOk()
            ->assertSee('<input type="hidden" name="activa" value="0">', false);
    }

    public function test_destroy_elimina_el_registro(): void
    {
        $editorial = Editorial::factory()->create();

        $this->delete("/editoriales/{$editorial->id}")->assertRedirect('/editoriales');

        $this->assertDatabaseMissing('editoriales', ['id' => $editorial->id]);
    }

    public function test_destroy_de_entidad_referenciada_es_rechazado(): void
    {
        $editorial = Editorial::factory()->create();
        Libro::factory()->for($editorial)->create();

        $this->delete("/editoriales/{$editorial->id}")->assertRedirect('/editoriales');

        $this->assertDatabaseHas('editoriales', ['id' => $editorial->id]);
    }
}
