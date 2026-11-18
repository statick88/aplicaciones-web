<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLectorRequest;
use App\Http\Requests\UpdateLectorRequest;
use App\Models\Lector;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('lectores.index', [
            'lectores' => Lector::query()->orderBy('nombre')->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('lectores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLectorRequest $request): RedirectResponse
    {
        Lector::create($request->validated());

        return redirect()->route('lectores.index')
            ->with('exito', 'Lector creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lector $lector): View
    {
        return view('lectores.show', [
            'lector' => $lector->load('prestamos.libro'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lector $lector): View
    {
        return view('lectores.edit', ['lector' => $lector]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLectorRequest $request, Lector $lector): RedirectResponse
    {
        $lector->update($request->validated());

        return redirect()->route('lectores.show', $lector)
            ->with('exito', 'Lector actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lector $lector): RedirectResponse
    {
        try {
            $lector->delete();

            return redirect()->route('lectores.index')
                ->with('exito', 'Lector eliminado correctamente.');
        } catch (QueryException) {
            return redirect()->route('lectores.index')
                ->with('error', 'No se puede eliminar: el lector tiene préstamos asociados.');
        }
    }
}
