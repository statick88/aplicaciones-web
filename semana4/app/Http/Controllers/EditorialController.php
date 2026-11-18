<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEditorialRequest;
use App\Http\Requests\UpdateEditorialRequest;
use App\Models\Editorial;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EditorialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('editoriales.index', [
            'editoriales' => Editorial::query()->orderBy('nombre')->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('editoriales.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEditorialRequest $request): RedirectResponse
    {
        Editorial::create($request->validated());

        return redirect()->route('editoriales.index')
            ->with('exito', 'Editorial creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Editorial $editorial): View
    {
        return view('editoriales.show', [
            'editorial' => $editorial->load('libros'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Editorial $editorial): View
    {
        return view('editoriales.edit', ['editorial' => $editorial]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEditorialRequest $request, Editorial $editorial): RedirectResponse
    {
        $editorial->update($request->validated());

        return redirect()->route('editoriales.show', $editorial)
            ->with('exito', 'Editorial actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Editorial $editorial): RedirectResponse
    {
        try {
            $editorial->delete();

            return redirect()->route('editoriales.index')
                ->with('exito', 'Editorial eliminada correctamente.');
        } catch (QueryException) {
            return redirect()->route('editoriales.index')
                ->with('error', 'No se puede eliminar: existen libros asociados a esta editorial.');
        }
    }
}
