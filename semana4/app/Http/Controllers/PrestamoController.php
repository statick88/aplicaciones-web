<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrestamoRequest;
use App\Http\Requests\UpdatePrestamoRequest;
use App\Models\Lector;
use App\Models\Libro;
use App\Models\Prestamo;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrestamoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('prestamos.index', [
            'prestamos' => Prestamo::query()
                ->with(['lector', 'libro'])
                ->orderByDesc('fecha_prestamo')
                ->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('prestamos.create', [
            'lectores' => Lector::query()->orderBy('nombre')->get(),
            'libros' => Libro::query()->orderBy('titulo')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePrestamoRequest $request): RedirectResponse
    {
        Prestamo::create($request->validated());

        return redirect()->route('prestamos.index')
            ->with('exito', 'Préstamo creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prestamo $prestamo): View
    {
        return view('prestamos.show', [
            'prestamo' => $prestamo->load(['lector', 'libro']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prestamo $prestamo): View
    {
        return view('prestamos.edit', [
            'prestamo' => $prestamo,
            'lectores' => Lector::query()->orderBy('nombre')->get(),
            'libros' => Libro::query()->orderBy('titulo')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePrestamoRequest $request, Prestamo $prestamo): RedirectResponse
    {
        $prestamo->update($request->validated());

        return redirect()->route('prestamos.show', $prestamo)
            ->with('exito', 'Préstamo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prestamo $prestamo): RedirectResponse
    {
        $prestamo->delete();

        return redirect()->route('prestamos.index')
            ->with('exito', 'Préstamo eliminado correctamente.');
    }
}
