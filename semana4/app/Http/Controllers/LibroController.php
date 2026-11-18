<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLibroRequest;
use App\Http\Requests\UpdateLibroRequest;
use App\Models\Categoria;
use App\Models\Editorial;
use App\Models\Libro;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LibroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('libros.index', [
            'libros' => Libro::query()
                ->with(['editorial', 'categoria'])
                ->orderBy('titulo')
                ->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('libros.create', [
            'editoriales' => Editorial::query()->orderBy('nombre')->get(),
            'categorias' => Categoria::query()->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLibroRequest $request): RedirectResponse
    {
        Libro::create($request->validated());

        return redirect()->route('libros.index')
            ->with('exito', 'Libro creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Libro $libro): View
    {
        return view('libros.show', [
            'libro' => $libro->load(['editorial', 'categoria', 'prestamos.lector']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Libro $libro): View
    {
        return view('libros.edit', [
            'libro' => $libro,
            'editoriales' => Editorial::query()->orderBy('nombre')->get(),
            'categorias' => Categoria::query()->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLibroRequest $request, Libro $libro): RedirectResponse
    {
        $libro->update($request->validated());

        return redirect()->route('libros.show', $libro)
            ->with('exito', 'Libro actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Libro $libro): RedirectResponse
    {
        try {
            $libro->delete();

            return redirect()->route('libros.index')
                ->with('exito', 'Libro eliminado correctamente.');
        } catch (QueryException) {
            return redirect()->route('libros.index')
                ->with('error', 'No se puede eliminar: el libro tiene préstamos asociados.');
        }
    }
}
